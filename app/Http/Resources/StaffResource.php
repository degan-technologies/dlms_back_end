<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'FirstName' => $this->FirstName,
            'LastName' => $this->LastName,
            'user_id' => $this->user_id,
            'library_branch_id' => $this->library_branch_id,
            'phone_no' => $this->phone_no,
            'email' => $this->email,
            'department' => $this->department,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
        ];
    }
}
