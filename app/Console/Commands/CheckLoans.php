<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Notifications\LoanStatusAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;

class CheckLoans extends Command
{
    use DispatchesJobs;
    protected $signature = 'loans:check {--type=all : Check type (overdue, upcoming, or all)}';
    protected $description = 'Check loan status and notify borrowers';

    public function handle()
    {
        $type = $this->option('type');
        $now = now();
        $today = $now->copy()->startOfDay();
        $notifiedCount = 0;

        $query = Loan::whereNull('returned_date')
            ->with(['book', 'user']);

        if ($type === 'overdue') {
            $query->whereDate('due_date', '<', $today);
            $this->info("Checking overdue loans...");
        } elseif ($type === 'upcoming') {
            $query->whereDate('due_date', '>=', $today)
                  ->whereDate('due_date', '<=', $today->copy()->addDays(3));
            $this->info("Checking upcoming due loans...");
        }

        $loans = $query->get();

        foreach ($loans as $loan) {
            $dueDate = Carbon::parse($loan->due_date);
            $daysDiff = $today->diffInDays($dueDate, false);
            
            if ($type === 'overdue') {
                // For overdue loans, notify every check (5 minutes)
                $this->sendNotification($loan, $daysDiff);
                $notifiedCount++;
            } 
            elseif ($type === 'upcoming') {
                // For upcoming loans, notify once per day
                if (!$loan->last_notified_at || !$loan->last_notified_at->isToday()) {
                    $this->sendNotification($loan, $daysDiff);
                    $notifiedCount++;
                }
            }
        }

        $this->info("Sent {$notifiedCount} notifications.");
        Log::info("Loan check completed. Type: {$type}. Notified: {$notifiedCount} loans.");
    }

    protected function sendNotification($loan, $daysDiff)
    {
        $loan->user->notify(new LoanStatusAlert($loan));
        $loan->update(['last_notified_at' => now()]);
    }
}