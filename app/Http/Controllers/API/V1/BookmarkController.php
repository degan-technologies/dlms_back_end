<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Bookmark\StoreBookmarkRequest;
use App\Http\Requests\V1\Bookmark\UpdateBookmarkRequest;
use App\Http\Resources\V1\Bookmark\BookmarkCollection;
use App\Http\Resources\V1\Bookmark\BookmarkResource;
use App\Models\Bookmark;
use App\Models\EBook;
use App\Models\OtherAsset;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Bookmark::where('user_id', Auth::id());
        
        // Filter by bookmarkable type
        if ($request->has('type')) {
            $type = $request->type;
            if ($type === 'ebook') {
                $query->where('bookmarkable_type', EBook::class);
            } elseif ($type === 'other_asset') {
                $query->where('bookmarkable_type', OtherAsset::class);
            }
        }
        
        // Filter by bookmarkable ID
        if ($request->has('bookmarkable_id')) {
            $query->where('bookmarkable_id', $request->bookmarkable_id);
        }
        
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        // Add eager loading for included relationships
        if (!empty($includes)) {
            $query->with($includes);
        }
        
        // Sort results
        $sortField = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');
        $allowedSorts = ['created_at', 'title', 'page_number'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 15);
        $bookmarks = $query->paginate($perPage);
        
        return new BookmarkCollection($bookmarks);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\Bookmark\StoreBookmarkRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBookmarkRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        
        // Determine the correct bookmarkable_type based on the type
        if ($validated['type'] === 'ebook') {
            $validated['bookmarkable_type'] = EBook::class;
        } elseif ($validated['type'] === 'other_asset') {
            $validated['bookmarkable_type'] = OtherAsset::class;
        }
        
        $bookmark = Bookmark::create($validated);
        
        return (new BookmarkResource($bookmark))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bookmark  $bookmark
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Bookmark $bookmark)
    {
        // Check if the bookmark belongs to the authenticated user
        if ($bookmark->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        if (!empty($includes)) {
            $bookmark->loadMissing($includes);
        }
        
        return new BookmarkResource($bookmark);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\Bookmark\UpdateBookmarkRequest  $request
     * @param  \App\Models\Bookmark  $bookmark
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBookmarkRequest $request, Bookmark $bookmark)
    {
        // Check if the bookmark belongs to the authenticated user
        if ($bookmark->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        $validated = $request->validated();
        
        $bookmark->update($validated);
        
        return new BookmarkResource($bookmark);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bookmark  $bookmark
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bookmark $bookmark)
    {
        // Check if the bookmark belongs to the authenticated user
        if ($bookmark->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        $bookmark->delete();
        
        return response()->json(['message' => 'Bookmark deleted successfully'], Response::HTTP_OK);
    }
}
