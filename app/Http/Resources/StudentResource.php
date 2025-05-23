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
            'grade_id' => $this->grade_id,
            'section_id' => $this->section_id,
            'gender' => $this->gender,
            'user_id' => $this->user_id,
            'library_branch_id' => $this->library_branch_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id ?? null,
                    'username' => $this->user->username ?? null,
                    'email' => $this->user->email ?? null,
                    'phone_no' => $this->user->phone_no ?? null,
                    'password'=>$this->user->password??null,
                ];
            }),
            'grade' => $this->whenLoaded('grade', function () {
                return [
                    'id' => $this->grade->id ?? null,
                    'name' => $this->grade->grade_name ?? null,
                
                ];
            }),
            'section' => $this->whenLoaded('section', function () {
                return [
                    'id' => $this->section->id ?? null,
                    'name' => $this->section->section_name ?? null,
                    
                ];
            }),
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
        ];
    }
}