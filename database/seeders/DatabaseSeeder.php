<?php

namespace Database\Seeders;

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
            GradeSeeder::class,
            SectionSeeder::class,
            CategorySeeder::class,
            ShelfSeeder::class,
            LanguageSeeder::class,
            SubjectSeeder::class,
            EbookTypeSeeder::class,
            NotificationTypeSeeder::class,
            RoleSeeder::class,
            StudentSeeder::class,
            StaffSeeder::class,
            
            // Books and related items
            BookItemSeeder::class,
            BookSeeder::class,
            BookConditionSeeder::class,
            EbookSeeder::class,
            
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
            AskLibrarianSeeder::class,
        ]);
    }
}
