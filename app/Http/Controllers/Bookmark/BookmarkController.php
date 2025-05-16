<?php

namespace App\Http\Controllers\Bookmark;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bookmark\StoreBookmarkRequest;
use App\Http\Requests\Bookmark\UpdateBookmarkRequest;
use App\Http\Resources\Bookmark\BookmarkCollection;
use App\Http\Resources\Bookmark\BookmarkResource;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookmarkController extends Controller
{
    /**
     * Display a listing of the bookmarks.
     */    public function index(Request $request)
    {
        $query = Bookmark::query();
        
        // Only show the current user's bookmarks
        $query->where('user_id', auth()->id());
        
        // Apply filters if provided
        if ($request->has('e_book_id')) {
            $query->where('e_book_id', $request->e_book_id);
        }
        
        // Filter by multiple ebook IDs
        if ($request->has('e_book_ids')) {
            $ebookIds = explode(',', $request->e_book_ids);
            $query->whereIn('e_book_id', $ebookIds);
        }
        
        // Search by title
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['ebook', 'user'];
            $query->with(array_intersect($relationships, $allowedRelations));
            
            // Special case for nested relationships
            if (in_array('ebook.bookItem', $relationships)) {
                $query->with('ebook.bookItem');
            }
        }
        
        // Custom sorting
        if ($request->has('sort_by') && in_array($request->sort_by, ['created_at', 'updated_at', 'title'])) {
            $direction = $request->has('sort_direction') && $request->sort_direction === 'asc' ? 'asc' : 'desc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->latest(); // Default sort by created_at desc
        }
        
        // Paginate the results
        $perPage = $request->per_page ?? 15;
        $bookmarks = $query->paginate($perPage);
        
        return new BookmarkCollection($bookmarks);
    }

    /**
     * Store a newly created bookmark in storage.
     */
    public function store(StoreBookmarkRequest $request)
    {
        $validated = $request->validated();
        
        // Add the authenticated user's ID
        $validated['user_id'] = auth()->id();
        
        // Create the bookmark
        $bookmark = Bookmark::create($validated);
        
        // Load relationships for the response
        $bookmark->load('ebook.bookItem');
        
        return new BookmarkResource($bookmark);
    }

    /**
     * Display the specified bookmark.
     */
    public function show(Request $request, Bookmark $bookmark)
    {
        // Check if the bookmark belongs to the authenticated user
        if ($bookmark->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['ebook', 'user'];
            $bookmark->load(array_intersect($relationships, $allowedRelations));
            
            // Special case for nested relationships
            if (in_array('ebook.bookItem', $relationships)) {
                $bookmark->load('ebook.bookItem');
            }
        }
        
        return new BookmarkResource($bookmark);
    }

    /**
     * Update the specified bookmark in storage.
     */
    public function update(UpdateBookmarkRequest $request, Bookmark $bookmark)
    {
        $validated = $request->validated();
        
        $bookmark->update($validated);
        
        // Load relationships for the response
        $bookmark->load('ebook.bookItem');
        
        return new BookmarkResource($bookmark);
    }

    /**
     * Remove the specified bookmark from storage.
     */
    public function destroy(Bookmark $bookmark)
    {
        // Check if the bookmark belongs to the authenticated user
        if ($bookmark->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        $bookmark->delete();
        
        return response()->json(['message' => 'Bookmark deleted successfully']);
    }
}
