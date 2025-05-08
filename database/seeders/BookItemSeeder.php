<?php

namespace Database\Seeders;

use App\Models\BookItem;
use Illuminate\Database\Seeder;

class BookItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Physical Books (for Book model)
        $physicalBooks = [
            [
                'title' => 'The Great Gatsby',
                'isbn' => '9780743273565',
                'item_type' => 'physical',
                'availability_status' => 'available',
                'author' => 'F. Scott Fitzgerald',
                'publication_year' => 1925,
                'description' => 'A novel of the Jazz Age, focusing on the mysterious millionaire Jay Gatsby and his obsession for Daisy Buchanan.',
                'cover_image_url' => 'https://example.com/images/gatsby.jpg',
                'language' => 'English',
                'library_branch_id' => 1, 
                'shelf_id' => 1, // F-A1
                'category_id' => 1, // Fiction
                'publisher_id' => 1, // Assuming Penguin Random House is ID 1
            ],
            [
                'title' => 'To Kill a Mockingbird',
                'isbn' => '9780061120084',
                'item_type' => 'physical',
                'availability_status' => 'available',
                'author' => 'Harper Lee',
                'publication_year' => 1960,
                'description' => 'The story of racial injustice and the loss of innocence in the American South during the Great Depression.',
                'cover_image_url' => 'https://example.com/images/mockingbird.jpg',
                'language' => 'English',
                'library_branch_id' => 1,
                'shelf_id' => 1, // F-A1
                'category_id' => 1, // Fiction
                'publisher_id' => 2, // Assuming HarperCollins is ID 2
            ],
            [
                'title' => 'The Principles of Quantum Mechanics',
                'isbn' => '9780198520115',
                'item_type' => 'physical',
                'availability_status' => 'available',
                'author' => 'Paul A. M. Dirac',
                'publication_year' => 1930,
                'description' => 'One of the classic textbooks on quantum mechanics.',
                'cover_image_url' => 'https://example.com/images/quantum.jpg',
                'language' => 'English',
                'library_branch_id' => 1,
                'shelf_id' => 4, // NF-A1
                'category_id' => 2, // Non-fiction
                'publisher_id' => 5, // Assuming Oxford University Press is ID 5
            ],
        ];
        
        // E-Books
        $ebooks = [
            [
                'title' => 'Learning PHP, MySQL & JavaScript',
                'isbn' => '9781491978917',
                'item_type' => 'ebook',
                'availability_status' => 'available',
                'author' => 'Robin Nixon',
                'publication_year' => 2018,
                'description' => 'A step-by-step guide to creating dynamic websites using popular web technologies.',
                'cover_image_url' => 'https://example.com/images/webdev.jpg',
                'language' => 'English',
                'library_branch_id' => 1,
                'shelf_id' => null, // E-books don't need physical shelves
                'category_id' => 3, // Technology
                'publisher_id' => 8, // Assuming Wiley or similar is ID 8
            ],
            [
                'title' => 'Harry Potter and the Philosopher\'s Stone',
                'isbn' => '9781781100271',
                'item_type' => 'ebook',
                'availability_status' => 'available',
                'author' => 'J.K. Rowling',
                'publication_year' => 1997,
                'description' => 'The first novel in the Harry Potter series about a young wizard who discovers his magical heritage.',
                'cover_image_url' => 'https://example.com/images/harrypotter.jpg',
                'language' => 'English',
                'library_branch_id' => 1,
                'shelf_id' => null, // E-books don't need physical shelves
                'category_id' => 4, // Children's Literature
                'publisher_id' => 6, // Assuming Bloomsbury is ID 6
            ],
        ];
        
        // Other Assets
        $otherAssets = [
            [
                'title' => 'Introduction to Machine Learning - Course Videos',
                'isbn' => 'ASSET001',
                'item_type' => 'other',
                'availability_status' => 'available',
                'author' => 'Prof. Andrew Smith',
                'publication_year' => 2023,
                'description' => 'A comprehensive video course on machine learning fundamentals.',
                'cover_image_url' => 'https://example.com/images/ml_course.jpg',
                'language' => 'English',
                'library_branch_id' => 1,
                'shelf_id' => 9, // DM-A1
                'category_id' => 3, // Technology
                'publisher_id' => 9, // MIT Press
            ],
            [
                'title' => 'World Atlas Collection',
                'isbn' => 'ASSET002',
                'item_type' => 'other',
                'availability_status' => 'available',
                'author' => 'National Geographic',
                'publication_year' => 2022,
                'description' => 'A collection of detailed world maps and geographical information.',
                'cover_image_url' => 'https://example.com/images/atlas.jpg',
                'language' => 'English',
                'library_branch_id' => 1,
                'shelf_id' => 4, // NF-A1
                'category_id' => 2, // Non-fiction
                'publisher_id' => 3, // Simon & Schuster
            ],
        ];
        
        // Insert all book items
        $this->insertBookItems($physicalBooks);
        $this->insertBookItems($ebooks);
        $this->insertBookItems($otherAssets);
    }
    
    /**
     * Helper method to insert book items
     */
    private function insertBookItems($items)
    {
        foreach ($items as $item) {
            BookItem::updateOrCreate(
                ['isbn' => $item['isbn']],
                $item
            );
        }
    }
}