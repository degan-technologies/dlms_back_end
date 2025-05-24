<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BookItem\StoreBookItemRequest;
use App\Http\Requests\V1\BookItem\UpdateBookItemRequest;
use App\Http\Resources\V1\BookItem\BookItemCollection;
use App\Http\Resources\V1\BookItem\BookItemResource;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookItemController extends Controller
{
    /**
     * Display a listing of the resource with filtering, sorting, and pagination.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = BookItem::query();
        
        // Apply filters - basic search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('author', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('isbn', 'like', '%' . $search . '%');
            });
        }
        
        // Apply specific filters
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        
        if ($request->has('author')) {
            $query->where('author', 'like', '%' . $request->author . '%');
        }
        
        if ($request->has('isbn')) {
            $query->where('isbn', 'like', '%' . $request->isbn . '%');
        }
        
        if ($request->has('item_type')) {
            $query->where('item_type', $request->item_type);
        }
        
       if ($request->has('availability_status')) {
            // Accept comma-separated list: e.g., 'Available,Checked Out'
            $statuses = explode(',', $request->query('availability_status'));
            $query->whereIn('availability_status', $statuses);
        }

        // Filter by grade
        if ($request->has('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }
        
        // Filter by language
        if ($request->has('language_id')) {
            $query->where('language_id', $request->language_id);
        }
        
        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by new arrivals
        if ($request->has('is_new_arrival') && $request->is_new_arrival) {
            $query->where('is_new_arrival', true);
        }
        
        // Filter by asset type (for OtherAsset)
        if ($request->has('asset_type_id')) {
            $query->whereHas('otherAsset', function($q) use ($request) {
                $q->where('asset_type_id', $request->asset_type_id);
            });
        }
        
        // Filter by tags
        if ($request->has('tag_ids') && is_array($request->tag_ids)) {
            $query->whereHas('tags', function($q) use ($request) {
                $q->whereIn('tags.id', $request->tag_ids);
            }, '=', count($request->tag_ids)); // Ensure all tags are matched
        }
        
        if ($request->has('publisher_id')) {
            $query->where('publisher_id', $request->publisher_id);
        }
        
        // Always include core relationships for consistent frontend display
        $defaultRelations = ['book', 'ebook', 'otherAsset', 'tags', 'category', 'shelf.section', 'library.libraryBranch'];
        
        // Additional relationships if requested in 'include' parameter
        $includes = $request->query('include', '');
        if (!empty($includes)) {
            $includedRelations = array_filter(explode(',', $includes));
            $relationsToLoad = array_merge($defaultRelations, $includedRelations);
            $query->with($relationsToLoad);
        } else {
            $query->with($defaultRelations);
        }
        
        // Apply sorting
        $sortBy = $request->query('sort_by', 'newest');
        
        switch ($sortBy) {
            case 'views':
                // Sort by view count (most viewed first)
                $query->leftJoin('recently_vieweds', 'book_items.id', '=', 'recently_vieweds.book_item_id')
                    ->select('book_items.*', DB::raw('COUNT(recently_vieweds.id) as view_count'))
                    ->groupBy('book_items.id')
                    ->orderByDesc('view_count');
                break;
            
            case 'alphabetical':
                $query->orderBy('title');
                break;
                
            case 'rating':
                // If you have ratings, implement logic here
                $query->orderBy('title'); // Fallback to title if no ratings
                break;
                
            case 'newest':
            default:
                $query->orderByDesc('created_at');
                break;
        }
        
        // Additional specific relations if requested in 'with' parameter
        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $allowedRelations = ['grade', 'language', 'publisher'];
            $validRelations = array_intersect($allowedRelations, $relations);
            
            if (!empty($validRelations)) {
                $query->with($validRelations);
            }
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 15);
        $bookItems = $query->paginate($perPage);
        
        return new BookItemCollection($bookItems);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\BookItem\StoreBookItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBookItemRequest $request)
    {
        $validated = $request->validated();
        
        $bookItem = BookItem::create($validated);
        
        return new BookItemResource($bookItem);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BookItem  $bookItem
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, BookItem $bookItem)
    {
        // Always load core relationships for consistent frontend display
        $defaultRelations = [
            'book', 
            'ebook', 
            'otherAsset', 
            'tags', 
            'category', 
            'shelf.section', 
            'library.libraryBranch'
        ];
        
        // Check if authenticated user for user-specific data
        $user = $request->user();
        if ($user) {
            // Load all notes and bookmarks tied to this asset, filtering by user
            // For direct bookitem notes/bookmarks
            $bookItem->load([
                'notes' => function($query) use ($user) {
                    $query->where('user_id', $user->id)->latest();
                },
                'bookmarks' => function($query) use ($user) {
                    $query->where('user_id', $user->id)->latest();
                }
            ]);
            
            // For ebook-specific notes/bookmarks (polymorphic)
            if ($bookItem->item_type === BookItem::TYPE_EBOOK) {
                $bookItem->load(['ebook' => function($query) use ($user) {
                    $query->with([
                        'notes' => function($q) use ($user) {
                            $q->where('user_id', $user->id)->latest();
                        },
                        'bookmarks' => function($q) use ($user) {
                            $q->where('user_id', $user->id)->latest();
                        }
                    ]);
                }]);
            } 
            
            // For other asset-specific notes/bookmarks (polymorphic)
            elseif ($bookItem->item_type === BookItem::TYPE_OTHER) {
                $bookItem->load(['otherAsset' => function($query) use ($user) {
                    $query->with([
                        'notes' => function($q) use ($user) {
                            $q->where('user_id', $user->id)->latest();
                        },
                        'bookmarks' => function($q) use ($user) {
                            $q->where('user_id', $user->id)->latest();
                        }
                    ]);
                }]);
            }
        }
        
        // Load chat messages related to this book item
        $bookItem->load(['chatMessages' => function($query) use ($user) {
            $query->latest()->limit(20); // Load most recent 20 chat messages
            
            // If not admin, only load user's own non-anonymous messages and all anonymous messages
            if ($user && !$user->hasRole('admin')) {
                $query->where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('is_anonymous', true);
                });
            }
        }]);
        
        $bookItem->loadMissing($defaultRelations);
        
        // Include additional relationships if requested
        $includes = $request->query('include', '');
        if (!empty($includes)) {
            $additionalIncludes = array_filter(explode(',', $includes));
            
            // Map snake_case relationship names to camelCase for Laravel's conventions
            $relationMappings = [
                'chat_messages' => 'chatMessages',
                'reading_lists' => 'readingLists'
            ];
            
            // Replace snake_case with camelCase where needed
            $processedIncludes = array_map(function($include) use ($relationMappings) {
                return $relationMappings[$include] ?? $include;
            }, $additionalIncludes);
            
            $bookItem->loadMissing($processedIncludes);
        }
        
        // Track this view for the user if authenticated
        if (auth()->check() && !$request->query('skip_tracking', false)) {
            $this->trackView($request->user(), $bookItem);
        }
        
        return new BookItemResource($bookItem);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\BookItem\UpdateBookItemRequest  $request
     * @param  \App\Models\BookItem  $bookItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBookItemRequest $request, BookItem $bookItem)
    {
        $validated = $request->validated();
        
        $bookItem->update($validated);
        
        return new BookItemResource($bookItem);
    }

    /**
     * Get related book items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BookItem  $bookItem
     * @return \Illuminate\Http\Response
     */
    public function related(Request $request, BookItem $bookItem)
    {
        // Start with explicitly defined related items
        $relatedItems = $bookItem->relatedBookItems();
        
        // If no explicit relations or we want additional related items
        if ($relatedItems->count() < 5 || $request->query('include_similar', true)) {
            // Get items with same category
            $sameCategoryItems = BookItem::where('id', '!=', $bookItem->id)
                ->where('category_id', $bookItem->category_id)
                ->take(5)
                ->get();
                
            // Get items with same grade
            $sameGradeItems = BookItem::where('id', '!=', $bookItem->id)
                ->where('grade_id', $bookItem->grade_id)
                ->take(5)
                ->get();
                
            // Get items with same tags
            $tagIds = $bookItem->tags->pluck('id')->toArray();
            $sameTagsItems = [];
            if (!empty($tagIds)) {
                $sameTagsItems = BookItem::where('id', '!=', $bookItem->id)
                    ->whereHas('tags', function($q) use ($tagIds) {
                        $q->whereIn('tags.id', $tagIds);
                    })
                    ->take(5)
                    ->get();
            }
            
            // Merge all related collections, removing duplicates
            $allRelated = $relatedItems->concat($sameCategoryItems)
                ->concat($sameGradeItems)
                ->concat($sameTagsItems)
                ->unique('id')
                ->take(10);
                
            return new BookItemCollection($allRelated);
        }
        
        return new BookItemCollection($relatedItems);
    }

    
    
    /**
     * Get new arrival book items.
     * These are items that were added recently and marked as new arrivals.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function newArrivals(Request $request)
    {
        $query = BookItem::query();
        
        // Get items marked as new arrivals
        $query->where('is_new_arrival', true);
        
        // Apply filters - basic search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('author', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('isbn', 'like', '%' . $search . '%');
            });
        }
        
        // Apply specific filters
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        
        if ($request->has('author')) {
            $query->where('author', 'like', '%' . $request->author . '%');
        }
        
        if ($request->has('item_type')) {
            $query->where('item_type', $request->item_type);
        }
        
        // Filter by category, grade, language
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->has('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }
        
        if ($request->has('language_id')) {
            $query->where('language_id', $request->language_id);
        }
        
        // Always include core relationships for consistent frontend display
        $defaultRelations = ['book', 'ebook', 'otherAsset', 'tags', 'category', 'shelf.section', 'library.libraryBranch'];
        
        // Additional relationships if requested in 'include' parameter
        $includes = $request->query('include', '');
        if (!empty($includes)) {
            $includedRelations = array_filter(explode(',', $includes));
            $relationsToLoad = array_merge($defaultRelations, $includedRelations);
            $query->with($relationsToLoad);
        } else {
            $query->with($defaultRelations);
        }
        
        // Apply sorting
        $sortBy = $request->query('sort_by', 'newest');
        
        switch ($sortBy) {
            case 'alphabetical':
                $query->orderBy('title');
                break;
                
            case 'views':
                $query->leftJoin('recently_vieweds', 'book_items.id', '=', 'recently_vieweds.book_item_id')
                    ->select('book_items.*', DB::raw('COUNT(recently_vieweds.id) as view_count'))
                    ->groupBy('book_items.id')
                    ->orderByDesc('view_count');
                break;
                
            case 'newest':
            default:
                $query->orderByDesc('created_at');
                break;
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 10);
        $newArrivals = $query->paginate($perPage);
        
        return new BookItemCollection($newArrivals);
    }

    /**
     * Track view of book item for a user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BookItem  $bookItem
     */
    protected function trackView($user, BookItem $bookItem)
    {
        // Find existing view record or create new one
        $recentlyViewed = \App\Models\RecentlyViewed::firstOrNew([
            'user_id' => $user->id,
            'book_item_id' => $bookItem->id,
        ]);
        
        // Update view count and timestamp
        $recentlyViewed->view_count = ($recentlyViewed->view_count ?? 0) + 1;
        $recentlyViewed->last_viewed_at = now();
        $recentlyViewed->save();
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BookItem  $bookItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(BookItem $bookItem)
    {
        try {
            DB::beginTransaction();
            
            // Delete associated book, ebook or other asset based on item_type
            switch ($bookItem->item_type) {
                case 'physical':
                    if ($bookItem->book) {
                        $bookItem->book->delete();
                    }
                    break;
                case 'ebook':
                    if ($bookItem->ebook) {
                        $bookItem->ebook->delete();
                    }
                    break;
                case 'other':
                    if ($bookItem->otherAsset) {
                        $bookItem->otherAsset->delete();
                    }
                    break;
            }
            
            // Delete the book item
            $bookItem->delete();
            
            DB::commit();
            
            return response()->json(['message' => 'Book item deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete book item', 'error' => $e->getMessage()], 500);
        }
    }
}