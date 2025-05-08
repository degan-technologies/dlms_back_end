<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'SectionName' => 'Fiction',
                'library_branch_id' => 1, // Ensure this matches an existing library branch ID
            ],
            [
                'SectionName' => 'Non-Fiction',
                'library_branch_id' => 2,
            ],
            [
                'SectionName' => 'Science',
                'library_branch_id' => 3,
            ],
        ];

        foreach ($sections as $section) {
            Section::updateOrCreate(['SectionName' => $section['SectionName']], $section);
        }
    }
}