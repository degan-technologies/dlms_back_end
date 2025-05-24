<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $grades = Grade::all();
        $gradeSections = [
            0 => ['A','B','C','D','E','F','G'], 
            1 => ['A','B','C'],               
            2 => ['A','B','C','D'],            
            3 => ['A','B'],                    
            4 => ['A','B','C','D','E'],       
        ];
        $sectionId = 1;
        foreach ($grades as $idx => $grade) {
            $sections = $gradeSections[$idx] ?? ['A','B']; 
            foreach ($sections as $sectionName) {
                Section::updateOrCreate(
                    ['id' => $sectionId],
                    [
                        'name' => $sectionName,
                        'grade_id' => $grade->id,
                    ]
                );
                $sectionId++;
            }
        }
        $this->command->info('Sections seeded successfully.');
    }
}
