<?php

namespace App\Http\Resources\Loan;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Loan\BookItemResource;

class LoanResource extends JsonResource
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
            'user_id' => $this->user_id,
            'book_id' => $this->book_id,
            'book_title' => $this->book->title,
            'borrow_date' => $this->borrow_date,
            'due_date' => $this->due_date,
            'returned_date' => $this->returned_date,
            'library_id' => $this->library_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'fine' => $this->fine ? [
                'amount' => $this->fine->fine_amount,
                'status' => $this->fine->payment_status,
                'paid_at' => $this->fine->paid_at,
            ] : null,
        ];
    }
}
