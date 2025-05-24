<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ReadingList\StoreReadingListRequest;
use App\Http\Requests\V1\ReadingList\UpdateReadingListRequest;
use App\Http\Resources\V1\ReadingList\ReadingListCollection;
use App\Http\Resources\V1\ReadingList\ReadingListResource;
use App\Models\ReadingList;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReadingListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = ReadingList::where(function($q) {
            $q->where('user_id', Auth::id())
              ->orWhere('is_public', true);
        });
        
        // Filter by user's own lists only
        if ($request->has('my_lists_only') && $request->my_lists_only) {
            $query->where('user_id', Auth::id());
        }
        
        // Filter by title
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
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
        $allowedSorts = ['created_at', 'title', 'updated_at'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 15);
        $readingLists = $query->paginate($perPage);
        
        return new ReadingListCollection($readingLists);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\ReadingList\StoreReadingListRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReadingListRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        
        // Create the reading list
        $readingList = ReadingList::create($validated);
        
        // Add initial book items if provided
        if (isset($validated['book_items']) && is_array($validated['book_items'])) {
            foreach ($validated['book_items'] as $bookItemId) {
          if (BookItem::find($bookItemId)) {
              $readingList->bookItems()->attach($bookItemId, ['added_at' => now()]);
          }
            }
        }
        
        return (new ReadingListResource($readingList))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReadingList  $readingList
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, ReadingList $readingList)
    {
        // Check if the reading list is public or belongs to the authenticated user
        if (!$readingList->is_public && $readingList->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        if (!empty($includes)) {
            $readingList->loadMissing($includes);
        } else {
            // Default to loading book items
            $readingList->load('bookItems');
        }
        
        return new ReadingListResource($readingList);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\ReadingList\UpdateReadingListRequest  $request
     * @param  \App\Models\ReadingList  $readingList
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReadingListRequest $request, ReadingList $readingList)
    {
        // Check if the reading list belongs to the authenticated user
        if ($readingList->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        $validated = $request->validated();
        
        $readingList->update($validated);
        
        return new ReadingListResource($readingList);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReadingList  $readingList
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReadingList $readingList)
    {
        // Check if the reading list belongs to the authenticated user
        if ($readingList->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        try {
            DB::beginTransaction();
            
            // Detach all book items
            $readingList->bookItems()->detach();
            
            // Delete the reading list
            $readingList->delete();
            
            DB::commit();
            
            return response()->json(['message' => 'Reading list deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete reading list', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add a book item to the reading list.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReadingList  $readingList
     * @return \Illuminate\Http\Response
     */
    public function addBookItem(Request $request, ReadingList $readingList)
    {
        // Check if the reading list belongs to the authenticated user
        if ($readingList->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        $request->validate([
            'book_item_id' => 'required|exists:book_items,id',
            'notes' => 'nullable|string',
        ]);
        
        // Check if the book item is already in the reading list
        if ($readingList->bookItems()->where('book_item_id', $request->book_item_id)->exists()) {
            return response()->json(['message' => 'Book item already exists in the reading list'], Response::HTTP_BAD_REQUEST);
        }
        
        // Add the book item to the reading list
        $readingList->bookItems()->attach($request->book_item_id, [
            'added_at' => now(),
            'notes' => $request->notes,
        ]);
        
        return response()->json(['message' => 'Book item added to reading list successfully'], Response::HTTP_OK);
    }

    /**
     * Remove a book item from the reading list.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReadingList  $readingList
     * @return \Illuminate\Http\Response
     */
    public function removeBookItem(Request $request, ReadingList $readingList)
    {
        // Check if the reading list belongs to the authenticated user
        if ($readingList->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        $request->validate([
            'book_item_id' => 'required|exists:book_items,id',
        ]);
        
        // Remove the book item from the reading list
        $readingList->bookItems()->detach($request->book_item_id);
        
        return response()->json(['message' => 'Book item removed from reading list successfully'], Response::HTTP_OK);
    }
}
