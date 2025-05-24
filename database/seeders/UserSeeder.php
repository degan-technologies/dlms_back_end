<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Define users for each role
        $users = [
            [
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
            ],
            [
                'username' => 'librarian',
                'email' => 'librarian@gmail.com',
                'role' => 'librarian',
            ],
            [
                'username' => 'staff',
                'email' => 'staff@gmail.com',
                'role' => 'staff',
            ],
            [
                'username' => 'student',
                'email' => 'student@gmail.com',
                'role' => 'student',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['username' => $data['username']],
                [
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'library_branch_id' => 1, // Adjust if needed
                ]
            );

            // Assign role if it exists
            if (Role::where('name', $data['role'])->exists()) {
                $user->assignRole($data['role']);
            }
        }

        $this->command->info('UserSeeder: Admin, Librarian, Staff, and Student users seeded.');
    }
}
