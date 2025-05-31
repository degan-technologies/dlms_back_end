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
            );
        }

        $this->command->info('Notification types seeded successfully.');
    }
}
