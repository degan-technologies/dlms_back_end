<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Announcement;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AnnouncementSeeder extends Seeder
{
    public function run()
    {
        // Create or get the librarian role
        $librarianRole = Role::firstOrCreate(['name' => 'librarian']);
        
        // Get users with librarian role (using query builder)
        $librarians = User::whereHas('roles', function($query) {
            $query->where('name', 'librarian');
        })->get();
        
        // If no librarians exist, create some
        if ($librarians->isEmpty()) {
            $librarians = User::factory(3)
                ->create()
                ->each(function ($user) use ($librarianRole) {
                    $user->assignRole($librarianRole);
                });
        }

        // Create announcements for each librarian
        $librarians->each(function ($librarian) {
            Announcement::factory()
                ->count(5)
                ->create([
                    'user_id' => $librarian->id,
                    'is_published' => rand(0, 1)
                ]);
        });

        // Create specific test announcements
        Announcement::factory()->create([
            'user_id' => $librarians->first()->id,
            'title' => 'Library Closure Notice',
            'content' => 'The library will be closed on Monday for maintenance.',
            'is_published' => true
        ]);

        Announcement::factory()->create([
            'user_id' => $librarians->first()->id,
            'title' => 'New Book Arrivals',
            'content' => 'We have received new books this week. Come check them out!',
            'is_published' => true
        ]);
    }
}