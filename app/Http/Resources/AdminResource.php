<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'FirstName' => $this->FirstName,
            'LastName' => $this->LastName,
            'email' => $this->email,
            'phone_no' => $this->phone_no,
            'user_id' => $this->user_id,
            'library_branch_id' => $this->library_branch_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
