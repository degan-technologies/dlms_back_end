<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'FirstName' => $this->FirstName,
            'LastName' => $this->LastName,
            'Address' => $this->Address,
            'grade' => $this->grade,
            'section' => $this->section,
            'gender' => $this->gender,
            'phone_no' => $this->phone_no,
            'email' => $this->email,
            'user_id' => $this->user_id,
            'BranchID' => $this->BranchID,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
        ];
    }
}