<?php

namespace Database\Seeders;

use App\Models\Library;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        Library::create([
            'name' => 'Central Library',
            'address' => '123 Main Street',
            'contact_number' => '1234567890',
        ]);
    }
}
