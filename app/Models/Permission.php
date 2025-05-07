<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $primaryKey = 'PermissionID';
    protected $fillable = ['PermissionName'];

    public function roles()
    {
        return $this->belongsToMany(
            UserRole::class,
            'role_permission',
            'PermissionID','RoleID'
        );
    }
}

