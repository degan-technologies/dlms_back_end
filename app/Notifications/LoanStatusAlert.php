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
        ];
    }
    public function toMail($notifiable)
    {
        $userName = $notifiable->username ?? ($notifiable->username ?? 'Library User');
        $bookTitle = $this->loan->book->title ?? 'your book';
        $dueDate = $this->loan->due_date ? Carbon::parse($this->loan->due_date)->toFormattedDateString() : 'unknown date';
        $status = $this->getStatus();
        $message = $this->getMessage();

        $statusText = [
            'overdue' => 'Overdue',
            'due_today' => 'Due Today',
            'due_soon' => 'Due Soon',
            'upcoming' => 'Upcoming',
        ][$status] ?? ($status ? ucfirst($status) : 'Loan Reminder');

        return (new MailMessage)
            ->greeting("Hello {$userName},")
            ->line("This is a reminder about your loan for '{$bookTitle}'.")
            ->line("Due Date: {$dueDate}")
            ->line("Status: {$statusText}")
            ->line($message)
            ->line('Thank you for using our library service!');
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
        $days = (int) $this->daysDiff;

        if ($days > 3) {
            return "Upcoming: '{$title}' is due in {$days} days.";
        }
        if ($days >= 1 && $days <= 3) {
            return "Reminder: '{$title}' is due in {$days} day" . ($days !== 1 ? 's' : '') . ".";
        }
        if ($days === 0) {
            return "URGENT: '{$title}' is due today!";
        }
        if ($days < 0) {
            return "OVERDUE: '{$title}' is " . abs($days) . " day" . (abs($days) !== 1 ? 's' : '') . " late!";
        }
        return "Loan status update for '{$title}'.";
    }

    protected function getStatus()
    {
        $days = (int) $this->daysDiff;
        if ($days < 0) return 'overdue';
        if ($days === 0) return 'due_today';
        if ($days >= 1 && $days <= 3) return 'due_soon';
        if ($days > 3) return 'upcoming';
        return 'unknown';
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
