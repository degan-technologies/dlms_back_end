<?php

namespace Database\Seeders;

use App\Models\Fine;
use App\Models\Library;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FineSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding fines...');

        // Get all overdue loans (where due_date has passed and returned_date is null)
        $currentDate = Carbon::now();
        $overdueLoans = Loan::whereNull('returned_date')
                          ->where('due_date', '<', $currentDate)
                          ->get();
        
        // Also get some returned loans that were late
        $lateReturnedLoans = Loan::whereNotNull('returned_date')
                              ->whereRaw('returned_date > due_date')
                              ->get();

        $allLoansForFines = $overdueLoans->merge($lateReturnedLoans);
        
        if ($allLoansForFines->isEmpty()) {
            $this->command->warn('No overdue or late loans found. Please run LoanSeeder first.');
            return;
        }

        // Get libraries
        $libraries = Library::all();
        
        if ($libraries->isEmpty()) {
            $this->command->warn('No libraries found. Please seed library data first.');
            return;
        }

        // Create fines for overdue books
        foreach ($allLoansForFines as $loan) {
            // Calculate days overdue
            if ($loan->returned_date) {
                // For returned books, calculate based on actual return date
                $daysLate = Carbon::parse($loan->due_date)->diffInDays(Carbon::parse($loan->returned_date));
            } else {
                // For books not returned yet, calculate based on current date
                $daysLate = Carbon::parse($loan->due_date)->diffInDays($currentDate);
            }
            
            // Only create fine if actually late (due_date < returned_date or current date)
            if ($daysLate > 0) {
                // Fine amount: base fine ($5) + additional per day late ($0.50 per day)
                $fineAmount = 5 + ($daysLate * 0.5);
                
                // 30% chance the fine has been paid for returned books
                $isPaid = $loan->returned_date && rand(1, 10) <= 3;
                $paymentDate = $isPaid ? Carbon::parse($loan->returned_date)->addDays(rand(1, 5)) : null;
                
                Fine::create([
                    'fine_amount' => $fineAmount,
                    'fine_date' => $loan->due_date, // Fine created on due date
                    'reason' => "Overdue book: {$daysLate} days late",
                    'payment_date' => $paymentDate,
                    'payment_status' => $isPaid,
                    'receipt_path' => $isPaid ? "/receipts/fine_" . $loan->id . "_" . now()->timestamp . ".pdf" : null,
                    'library_id' => $loan->library_id,
                    'user_id' => $loan->user_id,
                    'loan_id' => $loan->id,
                ]);
            }
        }
        
        // Create a few additional random fines for damaged books
        $randomLoans = Loan::inRandomOrder()->limit(5)->get();
        
        foreach ($randomLoans as $loan) {
            // 50% chance the fine has been paid
            $isPaid = rand(0, 1) == 1;
            $paymentDate = $isPaid ? Carbon::now()->subDays(rand(1, 30)) : null;
            
            Fine::create([
                'fine_amount' => rand(10, 50),
                'fine_date' => Carbon::now()->subDays(rand(5, 60)),
                'reason' => "Damaged book",
                'payment_date' => $paymentDate,
                'payment_status' => $isPaid,
                'receipt_path' => $isPaid ? "/receipts/damage_" . $loan->id . "_" . now()->timestamp . ".pdf" : null,
                'library_id' => $loan->library_id,
                'user_id' => $loan->user_id,
                'loan_id' => $loan->id,
            ]);
        }
        
        $this->command->info('Fines seeded successfully.');
    }
}
