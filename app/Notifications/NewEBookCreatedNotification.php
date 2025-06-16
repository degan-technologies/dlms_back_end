<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;

class NewEBookCreatedNotification extends Notification
{
    public $ebook;
    public $creatorName;

    public function __construct($ebook, $creatorName)
    {
        $this->ebook = $ebook;
        $this->creatorName = $creatorName;
    }

    public function via($notifiable)
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        $title = $this->ebook->title ?? ($this->ebook->bookItem->title ?? 'an eBook');
        return (new MailMessage)
            ->subject('New eBook Available: ' . $title)
            ->greeting('Hello ' . ($notifiable->username ?? $notifiable->email ?? 'Student') . ',')
            ->line('A new eBook has been added by ' . $this->creatorName . '.')
            ->line('Title: ' . $title)
            ->line('You can now access this eBook in the library system.')
            ->line('Thank you for using ' . config('app.name') . '!');
    }

    public function toDatabase($notifiable)
    {
        $title = $this->ebook->title ?? ($this->ebook->bookItem->title ?? 'an eBook');
        Log::info('[DEBUG] toDatabase called for user', ['user_id' => $notifiable->id, 'ebook_id' => $this->ebook->id, 'title' => $title]);
        return [
            'ebook_id' => $this->ebook->id,
            'title' => $title,
            'creator' => $this->creatorName,
            'message' => 'A new eBook has been added: ' . $title,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
