<?php

namespace Database\Seeders;

use App\Models\Library;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        $libraries = [
            [
                'name' => 'General Library',
                'contact_number' => '+251911223344',
                'library_branch_id' => 1,
            ],
            [
                'name' => 'Science And Technology Library',
                'contact_number' => '+251922334455',
                'library_branch_id' => 1,
            ],
            [
                'name' => 'Medical Library',
                'contact_number' => '+251933445566',
                'library_branch_id' => 2,
            ],
        ];

        foreach ($libraries as $library) {
            Library::firstOrCreate(
                ['name' => $library['name']],
                $library
            );
        }

        $this->command->info('Libraries seeded successfully.');
    }
}
