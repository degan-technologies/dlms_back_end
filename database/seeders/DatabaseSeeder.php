<?php

namespace Database\Seeders;

use App\Models\EBook;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */    public function run(): void
    {
        $this->call([
            // Base tables
            LibraryBranchSeeder::class,
            LibrarySeeder::class,
            CategorySeeder::class,
            ShelfSeeder::class,
            LanguageSeeder::class,
            SubjectSeeder::class,
            GradeSeeder::class,
            SectionSeeder::class,
            EbookTypeSeeder::class,
            RoleSeeder::class,
            EBookSeeder::class,
            EbookReadingSeeder::class,

            NotificationTypeSeeder::class,
            AskLibrarianSeeder::class,

            // Books and related items
            BookItemSeeder::class,
            BookConditionSeeder::class,

            // Loan and reservation related
            LoanSeeder::class,
            FineSeeder::class,
            ReservationSeeder::class,

            // User content
            BookmarkSeeder::class,
            NoteSeeder::class,
            ChatMessageSeeder::class,
            RecentlyViewedSeeder::class,
            CollectionSeeder::class,
        ]);
    }
}
