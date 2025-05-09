<?php

namespace Database\Seeders;

use App\Models\Library;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        $libraries = [
            [
            'name' => 'Central Library Branch 1',
            'address' => '123 Main Street',
            'contact_number' => '1234567890',
            'library_branch_id' => 1
            ],
            [
            'name' => 'Central Library Branch 2',
            'address' => '223 Main Street',
            'contact_number' => '1234567891',
            'library_branch_id' => 2
            ],
            [
            'name' => 'Central Library Branch 3',
            'address' => '323 Main Street',
            'contact_number' => '1234567892',
            'library_branch_id' => 3
            ],
            [
            'name' => 'Central Library Branch 4',
            'address' => '423 Main Street',
            'contact_number' => '1234567893',
            'library_branch_id' => 4
            ],
            [
            'name' => 'Central Library Branch 5',
            'address' => '523 Main Street',
            'contact_number' => '1234567894',
            'library_branch_id' => 5
            ],
        ];

        foreach ($libraries as $library) {
            Library::create($library);
        }
    }
}
