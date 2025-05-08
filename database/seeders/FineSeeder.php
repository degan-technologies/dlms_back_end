<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fine;

class FineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $fines = [
            [
                'library_branch_id' => 1, // Ensure this branch exists in the 'library_branches' table
                'user_id' => 1,          // Ensure this user exists in the 'users' table
                'loan_id' => 1,          // Ensure this loan exists in the 'loans' table
                'fine_amount' => 50.00,
                'fine_date' => '2025-05-01',
                'reason' => 'Late return',
                'payment_date' => null,
                'payment_status' => 'Unpaid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'library_branch_id' => 1,
                'user_id' => 2,
                'loan_id' => 2, // Ensure this loan exists in the 'loans' table
                'fine_amount' => 30.00,
                'fine_date' => '2025-05-02',
                'reason' => 'Lost book',
                'payment_date' => null,
                'payment_status' => 'Unpaid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'library_branch_id' => 2,
                'user_id' => 3,
                'loan_id' => 3, // Ensure this loan exists in the 'loans' table
                'fine_amount' => 20.00,
                'fine_date' => '2025-05-03',
                'reason' => 'Damaged book',
                'payment_date' => null,
                'payment_status' => 'Unpaid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($fines as $fine) {
            if (\App\Models\Loan::find($fine['loan_id'])) {
                Fine::updateOrCreate([
                    'loan_id' => $fine['loan_id'],
                ], $fine);
            } else {
            }
        }
    }
}