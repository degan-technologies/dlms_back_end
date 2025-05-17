<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Student;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder {
    public function run(): void {
        $roles = ['superadmin', 'admin', 'librarian', 'teacher', 'student'];

        // Create roles
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        foreach ($roles as $role) {
            $user = User::firstOrCreate(
                ['username' => $role],
                [
                    'email' => $role . '@gmail.com',
                    'password' => Hash::make('password'),
                    'library_branch_id' => 1,
                ]
            );

            if (!$user->hasRole($role)) {
                $user->assignRole($role);
            }

            if ($role === 'student') {
                // Insert into students table
                Student::firstOrCreate([
                    'user_id' => $user->id,
                ], [
                    'first_name' => ucfirst($role),
                    'last_name' => 'User',
                    'gender' => 'male',
                    'adress' => '123 Main St',
                    'grade' => '10',
                    'section' => 'A',
                ]);
            } else {
                // Insert into staff table
                Staff::firstOrCreate([
                    'user_id' => $user->id,
                ], [
                    'first_name' => ucfirst($role),
                    'last_name' => 'User',
                    'department' => ucfirst($role) . ' Department',
                ]);
            }
        }

        $this->command->info('Roles, users, students, and staff seeded successfully.');
    }
}
