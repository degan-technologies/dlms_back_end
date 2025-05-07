<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $primaryKey = 'RoleID';
    protected $fillable = ['RoleName'];

    public function users()
    {
        return $this->hasMany(User::class,'role_id','RoleID');
    }
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permission',
            'RoleID','PermissionID'
        );
    }
}

