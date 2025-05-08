<?php

namespace App\Resource\Fine;

use Illuminate\Http\Resources\Json\JsonResource;

class FineResource extends JsonResource
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
            'fine_amount' => $this->fine_amount,
            'fine_date' => $this->fine_date,
            'reason' => $this->reason,
            'payment_date' => $this->payment_date,
            'payment_status' => $this->payment_status,
            'library_branch_id' => $this->library_branch_id,
            'user_id' => $this->user_id,
            'loan_id' => $this->loan_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}