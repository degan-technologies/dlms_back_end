<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EbookReadingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ebook_readings')->insert([
            [
                'user_id' => 1,
                'ebook_id' => 1,
                'read_count' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'ebook_id' => 2,
                'read_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'ebook_id' => 1,
                'read_count' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'ebook_id' => 3,
                'read_count' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
