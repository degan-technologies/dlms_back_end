<?php

namespace App\Http\Controllers\Bookmark;

use App\Http\Controllers\Controller;
use App\Http\Resources\Bookmark\BookmarkCollection;
use App\Http\Resources\Bookmark\BookmarkResource;
use App\Models\Bookmark;
use App\Models\EBook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookmarkController extends Controller {
    /**
     * Display a listing of the bookmarks for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        // Get the authenticated user
        $user = $request->user();

        // Query bookmarks for this user
        $query = Bookmark::where('user_id', $user->id);

        // Add sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Eager load relationships
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $validRelationships = array_intersect($relationships, ['ebook', 'user']);
            if (!empty($validRelationships)) {
                $query->with($validRelationships);
            }

            // If ebook is requested, also load the bookItem and ebookType relations
            if (in_array('ebook', $validRelationships)) {
                $query->with(['ebook.bookItem', 'ebook.ebookType']);
            }
        }

        // Paginate the results
        $perPage = $request->input('per_page', 15);
        $bookmarks = $query->paginate($perPage);

        return new BookmarkCollection($bookmarks);
    }

    /**
     * Store a newly created bookmark in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $request->validate([
            'e_book_id' => 'required|exists:e_books,id',
            'title' => 'nullable|string|max:255',
        ]);

        // Check if the user already has a bookmark for this ebook
        $existingBookmark = Bookmark::where('user_id', $request->user()->id)
            ->where('e_book_id', $request->e_book_id)
            ->first();

        if ($existingBookmark) {
            return response()->json([
                'message' => 'You have already bookmarked this ebook',
                'bookmark' => new BookmarkResource($existingBookmark)
            ], 200);
        }

        // Create the bookmark
        $bookmark = Bookmark::create([
            'user_id' => $request->user()->id,
            'e_book_id' => $request->e_book_id,
            'title' => $request->title ?? EBook::find($request->e_book_id)->bookItem->title ?? 'Bookmark',
        ]);

        // Load relationships for the response
        $bookmark->load(['ebook.bookItem']);

        return response()->json([
            'message' => 'Bookmark created successfully',
            'bookmark' => new BookmarkResource($bookmark)
        ], 201);
    }

    /**
     * Display the specified bookmark.
     *
     * @param  \App\Models\Bookmark  $bookmark
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Bookmark $bookmark) {
        // Authorize that the user owns this bookmark
        if ($bookmark->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Load relationships
        $bookmark->load(['ebook.bookItem']);

        return new BookmarkResource($bookmark);
    }

    /**
     * Update the specified bookmark in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bookmark  $bookmark
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bookmark $bookmark) {
        // Authorize that the user owns this bookmark
        if ($bookmark->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $bookmark->update([
            'title' => $request->title,
        ]);

        return response()->json([
            'message' => 'Bookmark updated successfully',
            'bookmark' => new BookmarkResource($bookmark)
        ]);
    }

    /**
     * Remove the specified bookmark from storage.
     *
     * @param  \App\Models\Bookmark  $bookmark
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Bookmark $bookmark) {
        // Authorize that the user owns this bookmark
        if ($bookmark->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bookmark->delete();

        return response()->json([
            'message' => 'Bookmark deleted successfully'
        ]);
    }
    /**
     * Remove a bookmark by e_book_id for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $ebookId
     * @return \Illuminate\Http\Response
     */
    public function destroyByEbookId(Request $request, $ebookId) {
        $bookmark = Bookmark::where('user_id', $request->user()->id)
            ->where('e_book_id', $ebookId)
            ->first();

        if (!$bookmark) {
            return response()->json(['message' => 'Bookmark not found'], 404);
        }

        $bookmark->delete();

        return response()->json(['message' => 'Bookmark deleted successfully']);
    }

    /**
     * Check if an ebook is bookmarked by the current user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $ebookId
     * @return \Illuminate\Http\Response
     */
    public function checkBookmark(Request $request, $ebookId) {
        $bookmark = Bookmark::where('user_id', $request->user()->id)
            ->where('e_book_id', $ebookId)
            ->first();

        $isBookmarked = !is_null($bookmark);

        return response()->json([
            'is_bookmarked' => $isBookmarked,
            'bookmark' => $isBookmarked ? new BookmarkResource($bookmark) : null
        ]);
    }
}
