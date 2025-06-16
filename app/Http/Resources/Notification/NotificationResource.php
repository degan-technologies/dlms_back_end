<?php

namespace App\Http\Resources\Notification;
use Illuminate\Support\Carbon;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->getNotificationTitle(),
            'message' => $this->data['message'] ?? null,
            'book' => [
                'title' => $this->data['book_title'] ?? null,
                'due_date' => $this->data['due_date'] ?? null,
                'days_overdue' => $this->getDaysOverdue(),
            ],
            'loan_id' => $this->data['loan_id'] ?? null,
            'status' => $this->read_at ? 'read' : 'unread',
            'created_at' => $this->created_at->toDateTimeString(),
            'time_ago' => $this->created_at->diffForHumans(),
            'actions' => [
                'mark_as_read' => route('notifications.read', $this->id),
                'view_loan' => $this->getLoanUrl(),
            ]
        ];
    }

    protected function getNotificationTitle()
    {
        return match($this->type) {
            'App\\Notifications\\LoanStatusAlert' => 'Book Overdue Notice',
            default => 'Library Notification'
        };
    }

    protected function getDaysOverdue()
    {
        if (!isset($this->data['due_date'])) return null;

        $dueDate = Carbon::parse($this->data['due_date'])->startOfDay();
        $today = now()->startOfDay();
        $daysOverdue = $today->diffInDays($dueDate, false) * -1;

        return (int) $daysOverdue;
    }

    protected function getLoanUrl()
    {
        return isset($this->data['loan_id']) 
            ? route('loans.show', $this->data['loan_id'])
            : null;
    }
}