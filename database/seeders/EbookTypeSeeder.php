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
            ['name' => 'AUDIO'],
            ['name' => 'VIDEO'],
        ];

        foreach ($ebookTypes as $type) {
            EbookType::firstOrCreate(
                ['name' => $type['name']]
            );
        }

        $this->command->info('Ebook types seeded successfully.');
    }
}
