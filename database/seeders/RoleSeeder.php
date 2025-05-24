<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Student;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder {
    public function run(): void {
        $roles = ['superadmin', 'admin', 'librarian', 'teacher', 'student'];
        $sections = Section::all();
        $grades = Grade::all();

        // Create roles
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        foreach (
            $roles as $role
        ) {
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
                // Seed 3 students with different section and grades (unique users)
                $studentData = [
                    [
                        'first_name' => 'Alice',
                        'last_name' => 'Smith',
                        'gender' => 'female',
                        'adress' => '123 Main St',
                        'grade_id' => $grades->get(0)?->id ?? 1,
                        'section_id' => $sections->get(0)?->id ?? 1,
                    ],
                    [
                        'first_name' => 'Bob',
                        'last_name' => 'Johnson',
                        'gender' => 'male',
                        'adress' => '456 Oak Ave',
                        'grade_id' => $grades->get(1)?->id ?? 2,
                        'section_id' => $sections->get(1)?->id ?? 2,
                    ],
                    [
                        'first_name' => 'Carol',
                        'last_name' => 'Williams',
                        'gender' => 'female',
                        'adress' => '789 Pine Rd',
                        'grade_id' => $grades->get(2)?->id ?? 3,
                        'section_id' => $sections->get(2)?->id ?? 3,
                    ],
                ];
                foreach ($studentData as $data) {
                    $studentUser = User::firstOrCreate(
                        ['username' => strtolower($data['first_name'])],
                        [
                            'email' => strtolower($data['first_name']) . '@gmail.com',
                            'password' => Hash::make('password'),
                            'library_branch_id' => 1,
                        ]
                    );
                    if (!$studentUser->hasRole('student')) {
                        $studentUser->assignRole('student');
                    }
                    Student::firstOrCreate([
                        'user_id' => $studentUser->id,
                    ], $data);
                }
                // Also seed the main student user for the role
                Student::firstOrCreate([
                    'user_id' => $user->id,
                ], [
                    'first_name' => ucfirst($role),
                    'last_name' => 'User',
                    'gender' => 'male',
                    'adress' => '123 Main St',
                    'grade_id' => $grades->get(3)?->id ?? 4,
                    'section_id' => $sections->get(3)?->id ?? 4,
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
