<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            ['id' => 1, 'name' => '1'],
            ['id' => 2, 'name' => '2'],
            ['id' => 3, 'name' => '3'],
            ['id' => 4, 'name' => '4'],
            ['id' => 5, 'name' => '5'],
            ['id' => 6, 'name' => '6'],
            ['id' => 7, 'name' => '7'],
            ['id' => 8, 'name' => '8'],
            ['id' => 9, 'name' => '9'],
            ['id' => 10, 'name' => '10'],
            ['id' => 11, 'name' => '11'],
            ['id' => 12, 'name' => '12'],
        ];
        foreach ($grades as $grade) {
            Grade::updateOrCreate(['id' => $grade['id']], ['name' => $grade['name']]);
        }
    }
}
