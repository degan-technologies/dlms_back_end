<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    // ... existing code ...
public function toArray($request)
{
    return [
        'id' => $this->id,
        'first_name' => $this->first_name,
        'last_name' => $this->last_name,
        'user_id' => $this->user_id,
        'library_branch_id' => $this->library_branch_id,
        // 'phone_no' => $this->phone_no,
        // 'email' => $this->email,
        'department' => $this->department,
        // Add the related user fields
        'user' => $this->whenLoaded('user', function () {
            return [
                'id' => $this->user->id ?? null,
                'username' => $this->user->username ?? null,
                'email' => $this->user->email ?? null,
                'phone_no' => $this->user->phone_no ?? null,

            ];
        }),
        // 'created_at' => $this->created_at,
        // 'updated_at' => $this->updated_at,
    ];
}
// ... existing code ...
}
