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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = BookItem::query();
        
        // Apply filters
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
            $query->where('availability_status', $request->availability_status);
        }
        
        if ($request->has('library_branch_id')) {
            $query->where('library_branch_id', $request->library_branch_id);
        }
        
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->has('publisher_id')) {
            $query->where('publisher_id', $request->publisher_id);
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
        $allowedSorts = ['title', 'author', 'isbn', 'publication_year', 'created_at'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 5);
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
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        if (!empty($includes)) {
            $bookItem->loadMissing($includes);
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