<?php

namespace Database\Seeders;

use App\Models\Shelf;
use Illuminate\Database\Seeder;

class ShelfSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Sample sections and branches must already exist in the database
        // This assumes section_id 1-5 and library_branch_id 1-3 exist
        
        $shelves = [
            // Fiction section shelves - Branch 1
            [
                'code' => 'F-A1',
                'location' => 'Fiction Area - Row A',
                'capacity' => 100,
                'is_active' => true,
                'section_id' => 1, // Fiction
                'library_branch_id' => 1, // Main Branch
            ],
            [
                'code' => 'F-A2',
                'location' => 'Fiction Area - Row A',
                'capacity' => 100,
                'is_active' => true,
                'section_id' => 1, // Fiction
                'library_branch_id' => 1, // Main Branch
            ],
            [
                'code' => 'F-B1',
                'location' => 'Fiction Area - Row B',
                'capacity' => 100,
                'is_active' => true,
                'section_id' => 1, // Fiction
                'library_branch_id' => 1, // Main Branch
            ],
            
            // Non-fiction section shelves - Branch 1
            [
                'code' => 'NF-A1',
                'location' => 'Non-Fiction Area - Row A',
                'capacity' => 120,
                'is_active' => true,
                'section_id' => 2, // Non-Fiction
                'library_branch_id' => 1, // Main Branch
            ],
            [
                'code' => 'NF-A2',
                'location' => 'Non-Fiction Area - Row A',
                'capacity' => 120,
                'is_active' => true,
                'section_id' => 2, // Non-Fiction
                'library_branch_id' => 1, // Main Branch
            ],
            
            // Reference section shelves - Branch 1
            [
                'code' => 'REF-A1',
                'location' => 'Reference Area - Row A',
                'capacity' => 80,
                'is_active' => true,
                'section_id' => 3, // Reference
                'library_branch_id' => 1, // Main Branch
            ],
            
            // Children section shelves - Branch 1
            [
                'code' => 'CH-A1',
                'location' => 'Children Area - Row A',
                'capacity' => 90,
                'is_active' => true,
                'section_id' => 4, // Children
                'library_branch_id' => 1, // Main Branch
            ],
            [
                'code' => 'CH-A2',
                'location' => 'Children Area - Row A',
                'capacity' => 90,
                'is_active' => true,
                'section_id' => 4, // Children
                'library_branch_id' => 1, // Main Branch
            ],
            
            // Digital Media section shelves - Branch 1
            [
                'code' => 'DM-A1',
                'location' => 'Digital Media Area - Row A',
                'capacity' => 70,
                'is_active' => true,
                'section_id' => 5, // Digital Media
                'library_branch_id' => 1, // Main Branch
            ],
            
            // Branch 2 shelves
            [
                'code' => 'BR2-F-A1',
                'location' => 'Fiction Area - Row A',
                'capacity' => 80,
                'is_active' => true,
                'section_id' => 1, // Fiction
                'library_branch_id' => 2, // North Branch
            ],
            [
                'code' => 'BR2-NF-A1',
                'location' => 'Non-Fiction Area - Row A',
                'capacity' => 80,
                'is_active' => true,
                'section_id' => 2, // Non-Fiction
                'library_branch_id' => 2, // North Branch
            ],
            
            // Branch 3 shelves
            [
                'code' => 'BR3-F-A1',
                'location' => 'Fiction Area - Row A',
                'capacity' => 60,
                'is_active' => true,
                'section_id' => 1, // Fiction
                'library_branch_id' => 3, // South Branch
            ],
            [
                'code' => 'BR3-NF-A1',
                'location' => 'Non-Fiction Area - Row A',
                'capacity' => 60,
                'is_active' => true,
                'section_id' => 2, // Non-Fiction
                'library_branch_id' => 3, // South Branch
            ],
        ];
        
        foreach ($shelves as $shelf) {
            Shelf::updateOrCreate(
                ['code' => $shelf['code']],
                $shelf
            );
        }
    }
}