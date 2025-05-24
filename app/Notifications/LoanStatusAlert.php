<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class LoanStatusAlert extends Notification implements ShouldQueue
{
    use Queueable;
    public $tries = 3;
    public $backoff = [5, 10, 15];
    public $loan;
    public $daysDiff;

    public function __construct($loan)
    {
        $this->loan = $loan;
        $this->daysDiff = Carbon::today()->diffInDays(Carbon::parse($loan->due_date), false);
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'loan_id' => $this->loan->id,
            'book_title' => $this->loan->book->title ?? 'Unknown Book',
            'due_date' => $this->loan->due_date,
            'days_diff' => $this->daysDiff,
            'message' => $this->getMessage(),
            'status' => $this->getStatus(),
            'created_at' => now()->toDateTimeString(),
            'actions' => [
                'view_loan' => route('loans.show', $this->loan->id),
                'renew' => $this->daysDiff > 0 ? route('loans.renew', $this->loan->id) : null,
            ]
        ];
    }
public function toMail($notifiable)
{
    $userName = $notifiable->name ?? 'User'; // fallback if no name
    $loanStatus = $this->loan->status ?? 'unknown'; // example property, adjust to your model

    return (new MailMessage)
        ->greeting("Hello {$userName},")
        ->line("Your loan status has changed to {$loanStatus}.")
        ->action('View Loan Details', url("/loans/{$this->loan->id}"))
        ->line('Thank you for using our service!');
}


protected function getLoanStatusText()
{
    if ($this->loan->returned_date) {
        return 'Returned';
    }

    if ($this->daysDiff < 0) {
        return 'Pending';
    }

    return 'Overdue';
}




    protected function getMessage()
    {
        $title = $this->loan->book->title ?? 'your book';

        if ($this->daysDiff > 0) {
            return "Reminder: '{$title}' is due in {$this->daysDiff} day" . ($this->daysDiff !== 1 ? 's' : '') . "";
        }

        if ($this->daysDiff === 0) {
            return "URGENT: '{$title}' is due today!";
        }

        return "OVERDUE: '{$title}' is " . abs($this->daysDiff) . " day" . (abs($this->daysDiff) !== 1 ? 's' : '') . " late!";
    }

    protected function getStatus()
    {
        if ($this->daysDiff < 0) return 'overdue';
        if ($this->daysDiff === 0) return 'due_today';
        if ($this->daysDiff <= 3) return 'due_soon';
        return 'active';
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
