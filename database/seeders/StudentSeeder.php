<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Models\LibraryBranch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding students...');
        
        $faker = Faker::create();
        
        // Get library branches
        $libraryBranches = LibraryBranch::all();
        
        if ($libraryBranches->isEmpty()) {
            $this->command->warn('No library branches found. Please seed library branches first.');
            return;
        }
        
        // Create 50 students
        for ($i = 1; $i <= 10; $i++) {
            // Create a user with student role first
            $user = User::firstOrCreate(
                ['email' => 'student' . $i . '@example.com'],
                [
                    'username' => 'student' . $i,
                    'password' => Hash::make('password'),
                    'library_branch_id' => $libraryBranches->random()->id,
                ]
            );
            
            // Assign student role if not already assigned
            if (!$user->hasRole('student')) {
                $user->assignRole('student');
            }
            
            // Create a student profile linked to the user
            Student::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'FirstName' => $faker->firstName,
                    'LastName' => $faker->lastName,
                    'Address' => $faker->address,
                    'grade' => $faker->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12']),
                    'section' => $faker->randomElement(['A', 'B', 'C', 'D']),
                    'gender' => $faker->randomElement(['Male', 'Female']),
                    'BranchID' => $libraryBranches->random()->id,
                ]
            );
        }
        
        $this->command->info('Students seeded successfully.');
    }
}
