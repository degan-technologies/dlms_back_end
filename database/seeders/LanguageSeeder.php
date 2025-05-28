<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'Amharic', 'code' => 'am'],
            ['name' => 'Arabic', 'code' => 'ar'],
            ['name' => 'Chinese', 'code' => 'zh'],
            ['name' => 'French', 'code' => 'fr'],
            ['name' => 'German', 'code' => 'de'],
            ['name' => 'Italian', 'code' => 'it'],
            ['name' => 'Japanese', 'code' => 'ja'],
            ['name' => 'Korean', 'code' => 'ko'],
            ['name' => 'Portuguese', 'code' => 'pt'],
            ['name' => 'Russian', 'code' => 'ru'],
            ['name' => 'Spanish', 'code' => 'es'],
            ['name' => 'Swahili', 'code' => 'sw'],
            ['name' => 'Hindi', 'code' => 'hi'],
            ['name' => 'Oromo', 'code' => 'om'],
            ['name' => 'Tigrinya', 'code' => 'ti']
        ];

        foreach ($languages as $language) {
            Language::firstOrCreate(
                ['code' => $language['code']],
                $language
            );
        }

        $this->command->info('Languages seeded successfully.');
    }
}
