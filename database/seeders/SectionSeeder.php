<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Common library sections organized by content type
        $sections = [
            // Main Branch (ID 1) Sections
            [
                'section_name' => 'Fiction',
                'library_branch_id' => 1,
            ],
            [
                'section_name' => 'Non-Fiction',
                'library_branch_id' => 1,
            ],
            [
                'section_name' => 'Reference',
                'library_branch_id' => 1,
            ],
            [
                'section_name' => 'Children',
                'library_branch_id' => 1,
            ],
            [
                'section_name' => 'Digital Media',
                'library_branch_id' => 1,
            ],
            [
                'section_name' => 'Periodicals',
                'library_branch_id' => 1,
            ],
            [
                'section_name' => 'Teen/Young Adult',
                'library_branch_id' => 1,
            ],
            
            // North Branch (ID 2) Sections
            [
                'section_name' => 'Fiction',
                'library_branch_id' => 2,
            ],
            [
                'section_name' => 'Non-Fiction',
                'library_branch_id' => 2,
            ],
            [
                'section_name' => 'Children',
                'library_branch_id' => 2,
            ],
            [
                'section_name' => 'Local History',
                'library_branch_id' => 2,
            ],
            
            // South Branch (ID 3) Sections
            [
                'section_name' => 'Fiction',
                'library_branch_id' => 3,
            ],
            [
                'section_name' => 'Non-Fiction',
                'library_branch_id' => 3,
            ],
            [
                'section_name' => 'Children',
                'library_branch_id' => 3,
            ],
            [
                'section_name' => 'Multimedia',
                'library_branch_id' => 3,
            ],
        ];

        foreach ($sections as $section) {
            Section::updateOrCreate(
                [
                    'section_name' => $section['section_name'],
                    'library_branch_id' => $section['library_branch_id']
                ],
                $section
            );
        }
    }
}