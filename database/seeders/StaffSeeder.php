<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
use App\Models\LibraryBranch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding staff members...');
        
        $faker = Faker::create();
        
        // Get library branches
        $libraryBranches = LibraryBranch::all();
        
        if ($libraryBranches->isEmpty()) {
            $this->command->warn('No library branches found. Please seed library branches first.');
            return;
        }
        
        // Create librarians (staff members with librarian role)
        for ($i = 1; $i <= 10; $i++) {
            // Create a user with librarian role first
            $user = User::firstOrCreate(
                ['email' => 'librarian' . $i . '@example.com'],
                [
                    'username' => 'librarian' . $i,
                    'password' => Hash::make('password'),
                    'library_branch_id' => $libraryBranches->random()->id,
                ]
            );
            
            // Assign librarian role if not already assigned
            if (!$user->hasRole('librarian')) {
                $user->assignRole('librarian');
            }
            
            // Create a staff profile linked to the user
            Staff::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'FirstName' => $faker->firstName,
                    'LastName' => $faker->lastName,
                    'library_branch_id' => $libraryBranches->random()->id,
                ]
            );
        }
        
        // Create regular staff members
        for ($i = 1; $i <= 15; $i++) {
            // Create a user with staff role first
            $user = User::firstOrCreate(
                ['email' => 'staff' . $i . '@example.com'],
                [
                    'username' => 'staff' . $i,
                    'password' => Hash::make('password'),
                    'library_branch_id' => $libraryBranches->random()->id,
                ]
            );
            
            // Assign staff role if not already assigned
            if (!$user->hasRole('admin')) {
                $user->assignRole('admin');
            }
            
            // Create a staff profile linked to the user
            Staff::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'FirstName' => $faker->firstName,
                    'LastName' => $faker->lastName,
                    'library_branch_id' => $libraryBranches->random()->id,
                ]
            );
        }
        
        $this->command->info('Staff members seeded successfully.');
    }
}
