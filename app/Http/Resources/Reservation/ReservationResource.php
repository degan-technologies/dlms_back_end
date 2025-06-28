<?php

namespace App\Http\Resources\Reservation;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reservation_date' => $this->reservation_date,
            'status' => $this->status,
            'expiration_time' => $this->expiration_time,
            'reservation_code' => $this->reservation_code,
            'user_id' => $this->user_id,
            'user_name' => $this->user ? $this->user->username : null,
            'book_title' => $this->book ? $this->book->title : null,
            'book_id' => $this->book_id,
            'book_item_id' => $this->book->book_item_id ?? null,
            'library_id' => $this->library_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
