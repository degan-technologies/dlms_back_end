<?php

namespace Database\Seeders;

use App\Models\Shelf;
use Illuminate\Database\Seeder;

class ShelfSeeder extends Seeder
{
    public function run(): void
    {
        $shelves = [
            [
                'code' => 'A-01',
                'location' => 'Ground Floor, North Wing',
                'library_id' => 1,
            ],
            [
                'code' => 'A-02',
                'location' => 'Ground Floor, North Wing',
                'library_id' => 1,
            ],
            [
                'code' => 'B-01',
                'location' => 'Ground Floor, East Wing',
                'library_id' => 1,
            ],
            [
                'code' => 'B-02',
                'location' => 'Ground Floor, East Wing',
                'library_id' => 1,
            ],
            [
                'code' => 'C-01',
                'location' => 'First Floor, West Wing',
                'library_id' => 2,
            ],
            [
                'code' => 'C-02',
                'location' => 'First Floor, West Wing',
                'library_id' => 2,
            ],
            [
                'code' => 'M-01',
                'location' => 'Second Floor, East Wing',
                'library_id' => 3,
            ],
            [
                'code' => 'M-02',
                'location' => 'Second Floor, East Wing',
                'library_id' => 3,
            ],
        ];

        foreach ($shelves as $shelf) {
            Shelf::firstOrCreate(
                ['code' => $shelf['code']],
                $shelf
            );
        }

        $this->command->info('Shelves seeded successfully.');
    }
}
