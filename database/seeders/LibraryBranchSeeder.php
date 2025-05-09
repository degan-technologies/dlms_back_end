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
        
        $branches = [
            [
                'branch_name' => 'Downtown Branch',
                'address' => '456 Market Street',
                'contact_number' => '0987654321',
                'email' => 'downtown@library.com',
                'opening_hours' => '9 AM - 5 PM',
            ],
            [
                'branch_name' => 'Eastside Branch',
                'address' => '789 Oak Avenue',
                'contact_number' => '0123456789',
                'email' => 'eastside@library.com',
                'opening_hours' => '8 AM - 6 PM',
            ],
            [
                'branch_name' => 'Westwood Branch',
                'address' => '234 Pine Street',
                'contact_number' => '0345678912',
                'email' => 'westwood@library.com',
                'opening_hours' => '10 AM - 7 PM',
            ],
            [
                'branch_name' => 'North County Branch',
                'address' => '567 Maple Road',
                'contact_number' => '0567891234',
                'email' => 'northcounty@library.com',
                'opening_hours' => '9 AM - 6 PM',
            ],

            [
                'branch_name' => 'Southside Branch',
                'address' => '890 Cedar Lane',
                'contact_number' => '0789123456',
                'email' => 'southside@library.com',
                'opening_hours' => '8 AM - 5 PM',
            ],

        ];

        foreach ($branches as $branch) {
            LibraryBranch::create($branch);
        }
    }
}
