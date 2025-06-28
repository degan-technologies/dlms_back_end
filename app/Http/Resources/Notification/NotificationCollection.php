<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Resources\Json\ResourceCollection;


class NotificationCollection extends ResourceCollection
{
    public $collects = NotificationResource::class;

    public function toArray($request)
    {
        return [
            'notifications' => $this->collection,
            'meta' => [
                'unread_count' => $request->user()->unreadNotifications()->count(),
                // 'pagination' => [
                //     'total' => $this->total(),
                //     'count' => $this->count(),
                //     'per_page' => $this->perPage(),
                //     'current_page' => $this->currentPage(),
                //     'total_pages' => $this->lastPage(),
                // // ],
                // 'links' => [
                //     'first' => $this->url(1),
                //     'last' => $this->url($this->lastPage()),
                //     'prev' => $this->previousPageUrl(),
                //     'next' => $this->nextPageUrl(),
                // ],
            ],
        ];
    }
}