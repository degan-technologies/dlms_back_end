<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'adress' => $this->adress,
            'grade' => [
                'id' => $this->grade_id,
                'name' => $this->grade->name ?? null
            ],
            'section' => [
                'id' => $this->section_id,
                'name' => $this->section->name ?? null
            ],
            'gender' => $this->gender,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id ?? null,
                    'username' => $this->user->username ?? null,
                    'email' => $this->user->email ?? null,
                    'phone_no' => $this->user->phone_no ?? null,
                    'password' => $this->user->password ?? null,
                    'library_branch_name' => $this->user->libraryBranch->branch_name ?? null,
                ];
            }),
        ];
    }
}
