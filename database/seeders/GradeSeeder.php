<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            ['name' => 'Grade 1'],
            ['name' => 'Grade 2'],
            ['name' => 'Grade 3'],
            ['name' => 'Grade 4'],
            ['name' => 'Grade 5'],
            ['name' => 'Grade 6'],
            ['name' => 'Grade 7'],
            ['name' => 'Grade 8'],
            ['name' => 'Grade 9'],
            ['name' => 'Grade 10'],
            ['name' => 'Grade 11'],
            ['name' => 'Grade 12'],
            ['name' => 'Freshman'],
            ['name' => 'Sophomore'],
            ['name' => 'Junior'],
            ['name' => 'Senior'],
            ['name' => 'Masters'],
            ['name' => 'PhD']
        ];

        foreach ($grades as $grade) {
            Grade::firstOrCreate(
                ['name' => $grade['name']]
            );
        }

        $this->command->info('Grades seeded successfully.');
    }
}
