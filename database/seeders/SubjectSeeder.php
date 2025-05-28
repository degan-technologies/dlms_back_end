<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Mathematics'],
            ['name' => 'Physics'],
            ['name' => 'Chemistry'],
            ['name' => 'Biology'],
            ['name' => 'Computer Science'],
            ['name' => 'Engineering'],
            ['name' => 'Medicine'],
            ['name' => 'Economics'],
            ['name' => 'Business'],
            ['name' => 'Political Science'],
            ['name' => 'History'],
            ['name' => 'Geography'],
            ['name' => 'Psychology'],
            ['name' => 'Sociology'],
            ['name' => 'Literature'],
            ['name' => 'Philosophy'],
            ['name' => 'Art'],
            ['name' => 'Music'],
            ['name' => 'Language Studies'],
            ['name' => 'Education'],
            ['name' => 'Environmental Science'],
            ['name' => 'Agriculture'],
            ['name' => 'Architecture'],
            ['name' => 'Law']
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['name' => $subject['name']]
            );
        }

        $this->command->info('Subjects seeded successfully.');
    }
}
