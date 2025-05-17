<?php

namespace Database\Seeders;

use App\Models\LibraryBranch;
use Illuminate\Database\Seeder;

class LibraryBranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'branch_name' => 'Main Campus Library',
                'address' => '123 University Avenue',
                'contact_number' => '+251911234567',
                'email' => 'main.library@example.com',
                'location' => 'Main Campus',
                'library_time' => json_encode([
                    'monday' => ['open' => '08:00', 'close' => '20:00'],
                    'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                    'wednesday' => ['open' => '08:00', 'close' => '20:00'],
                    'thursday' => ['open' => '08:00', 'close' => '20:00'],
                    'friday' => ['open' => '08:00', 'close' => '18:00'],
                    'saturday' => ['open' => '09:00', 'close' => '16:00'],
                    'sunday' => ['open' => '12:00', 'close' => '16:00'],
                ]),
            ],
            [
                'branch_name' => 'Science Library',
                'address' => '456 Science Park',
                'contact_number' => '+251922345678',
                'email' => 'science.library@example.com',
                'location' => 'Science Campus',
                'library_time' => json_encode([
                    'monday' => ['open' => '08:30', 'close' => '19:00'],
                    'tuesday' => ['open' => '08:30', 'close' => '19:00'],
                    'wednesday' => ['open' => '08:30', 'close' => '19:00'],
                    'thursday' => ['open' => '08:30', 'close' => '19:00'],
                    'friday' => ['open' => '08:30', 'close' => '17:00'],
                    'saturday' => ['open' => '10:00', 'close' => '15:00'],
                    'sunday' => ['open' => '00:00', 'close' => '00:00'],
                ]),
            ],
        ];

        foreach ($branches as $branch) {
            LibraryBranch::firstOrCreate(
                ['branch_name' => $branch['branch_name']],
                $branch
            );
        }

        $this->command->info('Library branches seeded successfully.');
    }
}
