<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Loan;

class LoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $loans = [
            [
                'student_id' => 1, // Ensure this user exists in the 'users' table
                'book_item_id' => 1, // Ensure this book item exists in the 'book_items' table
                'borrow_date' => '2025-05-01',
                'due_date' => '2025-05-15',
                'return_date' => null,
                'library_branch_id' => 1, // Ensure this branch exists in the 'library_branches' table
            ],
            [
                'student_id' => 2, // Ensure this user exists in the 'users' table
                'book_item_id' => 2, // Ensure this book item exists in the 'book_items' table
                'borrow_date' => '2025-05-02',
                'due_date' => '2025-05-16',
                'return_date' => '2025-05-10',
                'library_branch_id' => 1, // Ensure this branch exists in the 'library_branches' table
            ],
            [
                'student_id' => 3, // Ensure this user exists in the 'users' table
                'book_item_id' => 3, // Ensure this book item exists in the 'book_items' table
                'borrow_date' => '2025-05-03',
                'due_date' => '2025-05-17',
                'return_date' => null,
                'library_branch_id' => 2, // Ensure this branch exists in the 'library_branches' table
            ],
        ];

        foreach ($loans as $loan) {
            Loan::updateOrCreate([
                'student_id' => $loan['student_id'],
                'book_item_id' => $loan['book_item_id'],
            ], $loan);
        }
    }
}