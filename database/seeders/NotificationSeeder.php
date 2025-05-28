<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        DB::table('notifications')->insert([
            [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\LoanStatusAlert',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => 1,
                'data' => json_encode(['message' => 'Your book is due soon!']),
                'read_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\LoanStatusAlert',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => 2,
                'data' => json_encode(['message' => 'You have an overdue book!']),
                'read_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
