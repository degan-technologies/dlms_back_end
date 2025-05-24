<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shelf;
use App\Models\Section; // Make sure you have a Section model
use App\Models\LibraryBranch; // Make sure you have a LibraryBranch model

class ShelvesSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch some existing sections and library branches
        $section = Section::first();  // You can modify this if necessary to select specific section
        $libraryBranch = LibraryBranch::first();  // Same here for library branch, or specify if needed

        if ($section && $libraryBranch) {
            // Insert sample shelves
            Shelf::create([
                'ShelfCode' => 'SHELF001',
                'section_id' => $section->id,  // Use the fetched section ID
                'library_branch_id' => $libraryBranch->id,  // Use the fetched library branch ID
            ]);

            Shelf::create([
                'ShelfCode' => 'SHELF002',
                'section_id' => $section->id,  // Reference existing section
                'library_branch_id' => $libraryBranch->id,  // Reference existing library branch
            ]);

            Shelf::create([
                'ShelfCode' => 'SHELF003',
                'section_id' => $section->id,  // Reference existing section
                'library_branch_id' => $libraryBranch->id,  // Reference existing library branch
            ]);

            $this->command->info('Shelves table seeded with sample data.');
        } else {
            $this->command->error('No section or library branch found. Please make sure there are sections and library branches seeded first.');
        }
    }
}
