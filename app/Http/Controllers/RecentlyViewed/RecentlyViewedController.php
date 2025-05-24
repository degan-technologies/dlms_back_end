<?php

namespace App\Http\Controllers\RecentlyViewed;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecentlyViewed\StoreRecentlyViewedRequest;
use App\Http\Resources\RecentlyViewed\RecentlyViewedCollection;
use App\Http\Resources\RecentlyViewed\RecentlyViewedResource;
use App\Models\RecentlyViewed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecentlyViewedController extends Controller
{
    /**
     * Display a listing of the recently viewed items for the authenticated user.
     */
    public function index(Request $request)
    {
        $query = RecentlyViewed::query();
          // Only show the current user's recently viewed items
        $query->where('user_id', Auth::id());
        
        // Apply filters if provided
        if ($request->has('e_book_id')) {
            $query->where('e_book_id', $request->e_book_id);
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
        
        // Sort by last_viewed_at in descending order (most recent first)
        $query->orderBy('last_viewed_at', 'desc');
        
        // Get the results
        $recentlyViewed = $query->get();
        
        return new RecentlyViewedCollection($recentlyViewed);
    }

    /**
     * Store a newly created recently viewed item in storage.
     * Implements FIFO approach - only keeps the 5 most recent entries per user.
     */
    public function store(StoreRecentlyViewedRequest $request)
    {        $validated = $request->validated();
        $userId = Auth::id();
        
        try {
            DB::beginTransaction();
            
            // Check if this ebook is already in the user's recently viewed items
            $existingRecord = RecentlyViewed::where('user_id', $userId)
                ->where('e_book_id', $validated['e_book_id'])
                ->first();
            
            if ($existingRecord) {
                // Update the last_viewed_at timestamp
                $existingRecord->update(['last_viewed_at' => now()]);
                $recentlyViewed = $existingRecord;
            } else {
                // Create a new entry
                $recentlyViewed = RecentlyViewed::create([
                    'user_id' => $userId,
                    'e_book_id' => $validated['e_book_id'],
                    'last_viewed_at' => now(),
                ]);
                
                // Get the count of user's recently viewed items
                $count = RecentlyViewed::where('user_id', $userId)->count();
                
                // If more than 5 entries, delete the oldest one(s) (FIFO)
                if ($count > 5) {
                    RecentlyViewed::where('user_id', $userId)
                        ->orderBy('last_viewed_at', 'asc')
                        ->limit($count - 5)
                        ->delete();
                }
            }
            
            DB::commit();
            
            // Load relationships for the response
            $recentlyViewed->load('ebook.bookItem');
            
            return new RecentlyViewedResource($recentlyViewed);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to record recently viewed item',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
