<?php

namespace App\Http\Resources\Loan;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'student_id' => $this->student_id,
            'book_item_id' => $this->book_item_id,
            'borrow_date' => $this->borrow_date,
            'due_date' => $this->due_date,
            'return_date' => $this->return_date,
            'library_branch_id' => $this->library_branch_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'book_item' => [
                'id' => $this->bookItem->id,
                'title' => $this->bookItem->title,
                'isbn' => $this->bookItem->isbn,
                'item_type' => $this->bookItem->item_type,
                'availability_status' => $this->bookItem->availability_status,
                'author' => $this->bookItem->author,
                'publication_year' => $this->bookItem->publication_year,
                'description' => $this->bookItem->description,
                'cover_image_url' => $this->bookItem->cover_image_url,
                'language' => $this->bookItem->language,
            ],
            'fine' => $this->fine ? [
                'amount' => $this->fine->fine_amount,
                'status' => $this->fine->payment_status,
                'paid_at' => $this->fine->paid_at,
            ] : null,
        ];
    }
}