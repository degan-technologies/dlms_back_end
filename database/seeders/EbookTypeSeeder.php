<?php

namespace Database\Seeders;

use App\Models\EbookType;
use Illuminate\Database\Seeder;

class EbookTypeSeeder extends Seeder
{
    public function run(): void
    {
        $ebookTypes = [
            ['name' => 'PDF'],
            ['name' => 'EPUB'],
            ['name' => 'MOBI'],
            ['name' => 'AZW (Kindle)'],
            ['name' => 'IBA (Apple iBooks)'],
            ['name' => 'FB2'],
            ['name' => 'DJVU'],
            ['name' => 'LIT'],
            ['name' => 'HTML'],
            ['name' => 'TXT'],
            ['name' => 'RTF'],
            ['name' => 'DOC/DOCX'],
            ['name' => 'CBZ/CBR (Comics)'],
        ];

        foreach ($ebookTypes as $type) {
            EbookType::firstOrCreate(
                ['name' => $type['name']]
            );
        }

        $this->command->info('Ebook types seeded successfully.');
    }
}
