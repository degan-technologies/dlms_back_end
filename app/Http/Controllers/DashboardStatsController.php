<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use App\Models\Collection;
use App\Models\EBook;
use App\Models\Library;
use App\Models\LibraryBranch;
use App\Models\RecentlyViewed;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class DashboardStatsController extends Controller
{
    public function stats()
    {
        $user = Auth::user();

        switch ($user->roles[0]->name) {
            case 'superadmin':
                return response()->json([
                    'total_branches' => LibraryBranch::count(),
                    'total_libraries' => Library::count(),
                    'total_admins' => User::query()->role('admin')->count(),
                    'total_users' => User::count(),
                ]);

            case 'admin':
                return response()->json([
                    'total_users' => Role::whereIn('name', ['student', 'librarian', 'teacher'])->count(),
                    'total_books' => Book::count(),
                    'total_ebooks' => EBook::count(),
                    'total_collections' => Collection::count(),
                ]);
            case 'librarian':
                return response()->json([
                    'total_books' => Book::count(),
                    'total_borrowed_books' => BookItem::whereNotNull('user_id')->count(),
                    'total_categories' => Category::count(),
                    'total_ebooks' => EBook::count(),
                ]);

            default:
                return response()->json(['message' => 'Unauthorized'], 403);
        }
    }
}
