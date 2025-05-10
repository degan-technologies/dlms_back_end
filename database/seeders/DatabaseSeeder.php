<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Seed base data - should already exist in your system
        // Users and libraries should be seeded first as they are parent entities
        
        // Seed reference data - order matters due to relationships
        $this->call([
            // Library branches should already exist before sections
            LibraryBranchSeeder::class,
            RoleSeeder::class,
            LibrarySeeder::class, // Library branches
            SectionSeeder::class,     // Library sections - needed for shelves
            CategorySeeder::class,    // Categories
            PublisherSeeder::class,   // Publishers
            AssetTypeSeeder::class,   // Asset types
            ShelfSeeder::class,       // Shelves - depends on sections
        ]);
        
        // Seed library items - order is important due to relationships
        $this->call([
            BookItemSeeder::class,     // Base book items (parent)
            BookSeeder::class,         // Physical books (child)
            EBookSeeder::class,        // E-Books (child)
            OtherAssetSeeder::class,   // Other assets (child)
        ]);
        
        // Seed user library features
        $this->call([
            LibraryFeatureSeeder::class, // Bookmarks, Notes, Reading Lists, Recently Viewed
        ]);
    }
}
