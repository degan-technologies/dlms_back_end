<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 6); // Default to 6 per page
        $page = $request->input('page', 1);
        $user = Auth::user();
        $role = $user->roles[0]->name;

        $query = Category::whereHas('bookItems.books')
            ->with(['bookItems' => function ($query) {
                $query->has('books');
            }]);

        // Role-based filtering
        switch ($role) {
            case 'superadmin':
                // No additional filter
                break;
            case 'admin':
            case 'librarian':
                // Filter categories by user's library_branch_id
                $libraryBranchId = $user->library_branch_id;
                $query->whereHas('bookItems', function ($q) use ($libraryBranchId) {
                    $q->where('library_id', $libraryBranchId);
                });
                break;
            default:
                // For other roles, restrict further if needed
                $query->whereHas('bookItems', function ($q) use ($user) {
                    $q->where('created_by', $user->id);
                });
                break;
        }

        $categories = $query->paginate($perPage, ['*'], 'page', $page);

        // Calculate the sum of books for each category
        $categories->getCollection()->transform(function ($category) {
            $category->total_books = $category->bookItems->sum(function ($bookItem) {
                return $bookItem->books->count();
            });
            return $category;
        });

        // Return paginated response
        return response()->json([
            'data' => $categories->items(),
            'pagination' => [
                'total_records' => $categories->total(),
                'per_page' => $categories->perPage(),
                'current_page' => $categories->currentPage(),
                'total_pages' => $categories->lastPage(),
            ],
        ]);
    }
}
