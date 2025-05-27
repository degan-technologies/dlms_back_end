<?php

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['type' => 'Due Reminder'],
            ['type' => 'Return Alert'],
            ['type' => 'General Notification'],
        ];

        foreach ($types as $type) {
            NotificationType::firstOrCreate(
                ['type' => $type['type']],
                [
                    'id' => Str::uuid()->toString(),
                    'data' => '{}',
                    'notifiable_type' => 'App\\User', // or another model
                    'notifiable_id' => 1
                ]
            );
        }

        $this->command->info('Notification types seeded successfully.');
    }
}
