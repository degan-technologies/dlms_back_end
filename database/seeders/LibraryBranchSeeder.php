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

        $branches = [
            [
                'branch_name' => 'Downtown Branch',
                'address' => '456 Market Street',
                'contact_number' => '0987654321',
                'email' => 'downtown@library.com',
                'opening_hours' => '9 AM - 5 PM',
                'library_id' => $library->id,
            ],
            [
                'branch_name' => 'Eastside Branch',
                'address' => '123 East Street',
                'contact_number' => '0123456789',
                'email' => 'eastside@library.com',
                'opening_hours' => '10 AM - 6 PM',
                'library_id' => $library->id,
            ],
            [
                'branch_name' => 'Westside Branch',
                'address' => '789 West Avenue',
                'contact_number' => '9876543210',
                'email' => 'westside@library.com',
                'opening_hours' => '8 AM - 4 PM',
                'library_id' => $library->id,
            ],
        ];

        foreach ($branches as $branch) {
            LibraryBranch::updateOrCreate(['branch_name' => $branch['branch_name']], $branch);
        }
    }
}
