<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BookItem;

class BookItemSeeder extends Seeder
{
    public function run(): void
    {
        // Add sample book items
        BookItem::create([
            'isbn' => '978-3-16-148410-0',
            'item_type' => 'book',
            'availability_status' => 'available',
            'library_branch_id' => 1, // Make sure the branch exists
            'shelf_id' => 1,          // Make sure the shelf exists
            'category_id' => 1,       // Make sure the category exists
        ]);

        BookItem::create([
            'isbn' => '978-1-4028-9462-6',
            'item_type' => 'book',
            'availability_status' => 'checked_out',
            'library_branch_id' => 1,
            'shelf_id' => 1,
            'category_id' => 2,  // Make sure the category exists
        ]);

        BookItem::create([
            'isbn' => '978-0-452-28423-4',
            'item_type' => 'book',
            'availability_status' => 'available',
            'library_branch_id' => 2, // Different branch
            'shelf_id' => 2,          // Different shelf
            'category_id' => 3,       // Different category
        ]);

        // Add more book items as needed
        $this->command->info('BookItems table seeded.');
    }
}
