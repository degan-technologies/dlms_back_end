<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['category_name' => 'Fiction'],
            ['category_name' => 'Science Fiction'],
            ['category_name' => 'Fantasy'],
            ['category_name' => 'Mystery'],
            ['category_name' => 'Thriller'],
            ['category_name' => 'Horror'],
            ['category_name' => 'Historical Fiction'],
            ['category_name' => 'Romance'],
            ['category_name' => 'Western'],
            ['category_name' => 'Bildungsroman'],
            ['category_name' => 'Non-fiction'],
            ['category_name' => 'Autobiography'],
            ['category_name' => 'Biography'],
            ['category_name' => 'Memoir'],
            ['category_name' => 'Essay'],
            ['category_name' => 'Self-help'],
            ['category_name' => 'Health'],
            ['category_name' => 'Guide/How-to'],
            ['category_name' => 'Religion & Spirituality'],
            ['category_name' => 'Textbook'],
            ['category_name' => 'Science'],
            ['category_name' => 'History'],
            ['category_name' => 'Travel'],
            ['category_name' => 'True Crime'],
            ['category_name' => 'Humor'],
            ['category_name' => 'Reference']
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['category_name' => $category['category_name']]
            );
        }

        $this->command->info('Categories seeded successfully.');
    }
}
