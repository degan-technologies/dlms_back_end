<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RecentlyViewed\RecentlyViewedCollection;
use App\Http\Resources\V1\RecentlyViewed\RecentlyViewedResource;
use App\Models\RecentlyViewed;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RecentlyViewedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = RecentlyViewed::query();
        
        // Get items for the authenticated user
        $query->where('user_id', Auth::id());
        
        // Apply filters - basic search
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('bookItem', function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('author', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // Apply specific filters
        if ($request->has('page_number')) {
            $query->where('last_page_viewed', $request->page_number);
        }
        
        if ($request->has('view_count')) {
            $query->where('view_count', '>=', $request->view_count);
        }
        
        // Always include core relationships
        $defaultRelations = ['bookItem', 'bookItem.tags', 'bookItem.category'];
        
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
        $sortBy = $request->query('sort_by', 'last_viewed_at');
        $sortDirection = $request->query('sort_direction', 'desc');
        $allowedSorts = ['last_viewed_at', 'view_count', 'view_duration'];
        
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderByDesc('last_viewed_at');
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 15);
        $recentlyViewed = $query->paginate($perPage);
        
        return new RecentlyViewedCollection($recentlyViewed);
    }

    /**
     * Track a resource view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function trackView(Request $request)
    {
        $request->validate([
            'book_item_id' => 'required|exists:book_items,id',
            'page_number' => 'nullable|integer',
            'view_duration' => 'nullable|integer',
        ]);
        
        // Find or create a recently viewed record
        $recentlyViewed = RecentlyViewed::firstOrNew([
            'user_id' => Auth::id(),
            'book_item_id' => $request->book_item_id,
        ]);
        
        // Update the record
        $recentlyViewed->last_viewed_at = now();
        $recentlyViewed->view_count = $recentlyViewed->exists ? $recentlyViewed->view_count + 1 : 1;
        
        if ($request->has('page_number')) {
            $recentlyViewed->last_page_viewed = $request->page_number;
        }
        
        if ($request->has('view_duration')) {
            $recentlyViewed->view_duration = $request->view_duration;
        }
        
        $recentlyViewed->save();
        
        return new RecentlyViewedResource($recentlyViewed);
    }

    /**
     * Clear all recently viewed records for the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function clearAll()
    {
        RecentlyViewed::where('user_id', Auth::id())->delete();
        
        return response()->json(['message' => 'Recently viewed history cleared successfully'], Response::HTTP_OK);
    }

    /**
     * Remove a specific recently viewed record.
     *
     * @param  \App\Models\RecentlyViewed  $recentlyViewed
     * @return \Illuminate\Http\Response
     */
    public function destroy(RecentlyViewed $recentlyViewed)
    {
        // Check if the record belongs to the authenticated user
        if ($recentlyViewed->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        $recentlyViewed->delete();
        
        return response()->json(['message' => 'Item removed from recently viewed history'], Response::HTTP_OK);
    }
}
