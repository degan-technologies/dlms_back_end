<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Common library categories
        $categories = [
            [
                
                'category_name' => 'Fiction', // Adding both column formats for compatibility
            ],
            [
               
                'category_name' => 'Non-Fiction',
            ],
            [
                
                'category_name' => 'Technology',
            ],
            [
                
                'category_name' => 'Science',
            ],
            [
               
                'category_name' => 'History',
            ],
            [
                
                'category_name' => 'Biography',
            ],
            [
                
                'category_name' => 'Children\'s Literature',
            ],
            [
                
                'category_name' => 'Young Adult',
            ],
            [
                
                'category_name' => 'Mystery',
            ],
            [
               
                'category_name' => 'Romance',
            ],
            [
               
                'category_name' => 'Fantasy',
            ],
            [
                
                'category_name' => 'Science Fiction',
            ],
            [
               
                'category_name' => 'Self-Help',
            ],
            [
                
                'category_name' => 'Business',
            ],
            [
                
                'category_name' => 'Art & Photography',
            ],
            [
                
                'category_name' => 'Reference',
            ],
            [
                
                'category_name' => 'Academic',
            ],
            [
                
                'category_name' => 'Cooking',
            ],
            [
              
                'category_name' => 'Travel',
            ],
            [
               
                'category_name' => 'Poetry',
            ],
        ];

        foreach ($categories as $category) {
            // Using updateOrCreate to prevent duplicates
            // Using CategoryName for the check as that's what's in the model
            Category::updateOrCreate(
                [
                    'category_name' => $category['category_name'],
                ],
                $category
            );
        }
    }
}