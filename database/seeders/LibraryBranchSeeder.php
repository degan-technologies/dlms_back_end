<?php

namespace Database\Seeders;

use App\Models\Library;
use App\Models\LibraryBranch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LibraryBranchSeeder extends Seeder
{
    public function run(): void
    {
        $library = Library::first(); // assumes LibrarySeeder already ran

        LibraryBranch::create([
            'branch_name' => 'Downtown Branch',
            'address' => '456 Market Street',
            'contact_number' => '0987654321',
            'email' => 'downtown@library.com',
            'opening_hours' => '9 AM - 5 PM',
            'library_id' => $library->id,
        ]);
    }
}
