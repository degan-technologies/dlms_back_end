<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;


class EBookSeeder extends Seeder
{
    public function run()
    {
        DB::table('e_books')->insert([
            [
                'file_path'      => 'ebooks/sample_ebook_1.pdf',
                'file_name'      => 'Sample Ebook 1',
                'file_size_mb'   => 2.5,
                'pages'          => 120,
                'is_downloadable' => true,
                'user_id'        => 1,
                'book_item_id'   => 1,
                'e_book_type_id' => 1,
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ],
            [
                'file_path'      => 'ebooks/sample_ebook_2.pdf',
                'file_name'      => 'Sample Ebook 2',
                'file_size_mb'   => 1.8,
                'pages'          => 85,
                'is_downloadable' => false,
                'user_id'        => 2,
                'book_item_id'   => 2,
                'e_book_type_id' => 2,
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ],
            [
                'file_path'      => 'ebooks/sample_ebook_3.pdf',
                'file_name'      => 'Sample Ebook 3',
                'file_size_mb'   => 3.2,
                'pages'          => 200,
                'is_downloadable' => true,
                'user_id'        => 1,
                'book_item_id'   => 3,
                'e_book_type_id' => 1,
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ],
        ]);
    }
}
