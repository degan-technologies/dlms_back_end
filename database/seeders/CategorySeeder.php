<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categories = [
            ['category_name' => 'Fiction'],
            ['category_name' => 'Non-Fiction'],
            ['category_name' => 'Science'],
            ['category_name' => 'History'],
            ['category_name' => 'Biography'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['category_name' => $category['category_name']], $category);
        }
    }
}