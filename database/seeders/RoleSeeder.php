<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = ['super-admin', 'admin', 'librarian', 'staff', 'student'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Optionally create a super-admin user
        // Create a super-admin user
        $user = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
            'email' => 'sadmin@gmail.com',
            'password' => Hash::make('password'),
            'library_branch_id' => 1, // Update as appropriate
            ]
        );
        $user->assignRole('super-admin');

        // Create sample users for all roles
        foreach ($roles as $role) {
            $sampleUser = User::firstOrCreate(
            ['username' => $role],
            [
                'email' => $role . '@gmail.com',
                'password' => Hash::make('password'),
                'library_branch_id' => 1, // Update as appropriate
            ]
            );
            $sampleUser->assignRole($role);
        }

        $user->assignRole('super-admin');

        $this->command->info('Roles and super-admin user seeded.');
    }
}
