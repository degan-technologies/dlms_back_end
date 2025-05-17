<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        // For each grade (assuming grades 1-12 and college levels exist with IDs 1-18)
        for ($gradeId = 1; $gradeId <= 12; $gradeId++) {
            // Create sections A through D for each grade
            $sections = [
                ['name' => "Section A - Grade $gradeId", 'grade_id' => $gradeId],
                ['name' => "Section B - Grade $gradeId", 'grade_id' => $gradeId],
                ['name' => "Section C - Grade $gradeId", 'grade_id' => $gradeId],
                ['name' => "Section D - Grade $gradeId", 'grade_id' => $gradeId],
            ];

            foreach ($sections as $section) {
                Section::firstOrCreate(
                    ['name' => $section['name']],
                    $section
                );
            }
        }

        // For college levels (assuming grade IDs 13-18 for Freshman, Sophomore, Junior, Senior, Masters, PhD)
        for ($gradeId = 13; $gradeId <= 18; $gradeId++) {
            // Create departments for each college level
            $departments = [
                'Computer Science',
                'Engineering',
                'Medicine',
                'Business',
                'Arts'
            ];

            foreach ($departments as $dept) {
                $gradeName = match ($gradeId) {
                    13 => 'Freshman',
                    14 => 'Sophomore',
                    15 => 'Junior',
                    16 => 'Senior',
                    17 => 'Masters',
                    18 => 'PhD',
                    default => 'Unknown'
                };

                Section::firstOrCreate(
                    ['name' => "$dept - $gradeName"],
                    [
                        'name' => "$dept - $gradeName",
                        'grade_id' => $gradeId
                    ]
                );
            }
        }

        $this->command->info('Sections seeded successfully.');
    }
}
