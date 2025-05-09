<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder {
    public function run(): void {
        // Create roles
        $roles = ['superadmin', 'admin', 'librarian', 'staff', 'student'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create users for all roles
        foreach ($roles as $role) {
            // Use specific email for superadmin, and role-based emails for others

            $sampleUser = User::firstOrCreate(
                ['username' => $role],
                [
                    'email' => $role . '@gmail.com',
                    'password' => Hash::make('password'),
                    'library_branch_id' => 1, // Update as appropriate
                ]
            );

            // Ensure role is assigned only once
            if (!$sampleUser->hasRole($role)) {
                $sampleUser->assignRole($role);
            }
        }

        $this->command->info('Roles and users seeded successfully.');
    }
}
