<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EBook\StoreEBookRequest;
use App\Http\Requests\V1\EBook\UpdateEBookRequest;
use App\Http\Resources\V1\EBook\EBookCollection;
use App\Http\Resources\V1\EBook\EBookResource;
use App\Models\EBook;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EBookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = EBook::query()
            ->join('book_items', 'e_books.book_item_id', '=', 'book_items.id');
        
        // Apply filters
        if ($request->has('title')) {
            $query->where('book_items.title', 'like', '%' . $request->title . '%');
        }
        
        if ($request->has('author')) {
            $query->where('book_items.author', 'like', '%' . $request->author . '%');
        }
        
        if ($request->has('isbn')) {
            $query->where('book_items.isbn', 'like', '%' . $request->isbn . '%');
        }
        
        if ($request->has('file_format')) {
            $query->where('e_books.file_format', $request->file_format);
        }
        
        if ($request->has('is_downloadable')) {
            $query->where('e_books.is_downloadable', $request->boolean('is_downloadable'));
        }
        
        if ($request->has('requires_authentication')) {
            $query->where('e_books.requires_authentication', $request->boolean('requires_authentication'));
        }
        
        if ($request->has('availability_status')) {
            $query->where('book_items.availability_status', $request->availability_status);
        }
        
        if ($request->has('library_branch_id')) {
            $query->where('book_items.library_branch_id', $request->library_branch_id);
        }
        
        if ($request->has('category_id')) {
            $query->where('book_items.category_id', $request->category_id);
        }
        
        if ($request->has('publisher_id')) {
            $query->where('book_items.publisher_id', $request->publisher_id);
        }
        
        // Select ebooks
        $query->select('e_books.*');
        
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        // Add eager loading for included relationships
        if (!empty($includes)) {
            $query->with($includes);
            
            // Always include bookItem
            if (!in_array('bookItem', $includes)) {
                $query->with('bookItem');
            }
        } else {
            // Default to always include bookItem
            $query->with('bookItem');
        }
        
        // Sort results
        $sortField = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');
        $allowedSorts = ['file_format', 'file_size_mb', 'created_at'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy('e_books.' . $sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        } else if (in_array($sortField, ['title', 'author', 'isbn', 'publication_year'])) {
            $query->orderBy('book_items.' . $sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 15);
        $ebooks = $query->paginate($perPage);
        
        return new EBookCollection($ebooks);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\EBook\StoreEBookRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEBookRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            // Extract ebook-specific fields
            $ebookData = [
                'file_url' => $validated['file_url'],
                'file_format' => $validated['file_format'],
                'file_size_mb' => $validated['file_size_mb'],
                'pages' => $validated['pages'] ?? null,
                'is_downloadable' => $validated['is_downloadable'] ?? true,
                'requires_authentication' => $validated['requires_authentication'] ?? true,
                'drm_type' => $validated['drm_type'] ?? null,
                'access_expires_at' => $validated['access_expires_at'] ?? null,
                'max_downloads' => $validated['max_downloads'] ?? null,
                'reader_app' => $validated['reader_app'] ?? null,
            ];
            
            // Extract book item fields
            $bookItemData = array_diff_key($validated, $ebookData);
            $bookItemData['item_type'] = 'ebook';
            
            // Create book item
            $bookItem = BookItem::create($bookItemData);
            
            // Create ebook with reference to book item
            $ebookData['book_item_id'] = $bookItem->id;
            $ebook = EBook::create($ebookData);
            
            // Load the book item relationship for the response
            $ebook->load('bookItem');
            
            DB::commit();
            
            return new EBookResource($ebook);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create ebook', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EBook  $ebook
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, EBook $ebook)
    {
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        if (!empty($includes)) {
            // Always include bookItem
            if (!in_array('bookItem', $includes)) {
                $includes[] = 'bookItem';
            }
            
            $ebook->loadMissing($includes);
        } else {
            // Default to always include bookItem
            $ebook->load('bookItem');
        }
        
        return new EBookResource($ebook);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\EBook\UpdateEBookRequest  $request
     * @param  \App\Models\EBook  $ebook
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEBookRequest $request, EBook $ebook)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            // Extract ebook-specific fields
            $ebookData = array_intersect_key($validated, [
                'file_url' => '',
                'file_format' => '',
                'file_size_mb' => '',
                'pages' => '',
                'is_downloadable' => '',
                'requires_authentication' => '',
                'drm_type' => '',
                'access_expires_at' => '',
                'max_downloads' => '',
                'reader_app' => '',
            ]);
            
            // Extract book item fields - remove ebook-specific fields
            $bookItemData = array_diff_key($validated, [
                'file_url' => '',
                'file_format' => '',
                'file_size_mb' => '',
                'pages' => '',
                'is_downloadable' => '',
                'requires_authentication' => '',
                'drm_type' => '',
                'access_expires_at' => '',
                'max_downloads' => '',
                'reader_app' => '',
            ]);
            
            // Update ebook
            if (!empty($ebookData)) {
                $ebook->update($ebookData);
            }
            
            // Update book item if we have book item data
            if (!empty($bookItemData) && $ebook->bookItem) {
                $ebook->bookItem->update($bookItemData);
            }
            
            // Reload ebook with book item
            $ebook->load('bookItem');
            
            DB::commit();
            
            return new EBookResource($ebook);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update ebook', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EBook  $ebook
     * @return \Illuminate\Http\Response
     */
    public function destroy(EBook $ebook)
    {
        try {
            DB::beginTransaction();
            
            // Store book item ID before deleting the ebook
            $bookItemId = $ebook->book_item_id;
            
            // Delete the ebook
            $ebook->delete();
            
            // Delete the associated book item
            if ($bookItemId) {
                $bookItem = BookItem::find($bookItemId);
                if ($bookItem) {
                    $bookItem->delete();
                }
            }
            
            DB::commit();
            
            return response()->json(['message' => 'EBook deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete ebook', 'error' => $e->getMessage()], 500);
        }
    }
}