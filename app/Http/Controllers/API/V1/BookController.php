<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Book\StoreBookRequest;
use App\Http\Requests\V1\Book\UpdateBookRequest;
use App\Http\Resources\V1\Book\BookCollection;
use App\Http\Resources\V1\Book\BookResource;
use App\Models\Book;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Book::query();
        
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
        
        if ($request->has('barcode')) {
            $query->where('books.barcode', 'like', '%' . $request->barcode . '%');
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
        
        if ($request->has('reference_only')) {
            $query->where('books.reference_only', $request->boolean('reference_only'));
        }
        
        if ($request->has('cover_type')) {
            $query->where('books.cover_type', $request->cover_type);
        }
        
        // Select books
        $query->select('books.*');
        
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
        $allowedSorts = ['barcode', 'created_at'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy('books.' . $sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        } else if (in_array($sortField, ['title', 'author', 'isbn', 'publication_year'])) {
            $query->orderBy('book_items.' . $sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 15);
        $books = $query->paginate($perPage);
        
        return new BookCollection($books);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\Book\StoreBookRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBookRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            // Extract book-specific fields
            $bookData = [
                'edition' => $validated['edition'] ?? null,
                'pages' => $validated['pages'] ?? null,
                'cover_type' => $validated['cover_type'] ?? null,
                'dimensions' => $validated['dimensions'] ?? null,
                'weight_grams' => $validated['weight_grams'] ?? null,
                'barcode' => $validated['barcode'] ?? null,
                'shelf_location_detail' => $validated['shelf_location_detail'] ?? null,
                'reference_only' => $validated['reference_only'] ?? false,
            ];
            
            // Extract book item fields
            $bookItemData = array_diff_key($validated, $bookData);
            $bookItemData['item_type'] = 'physical';
            
            // Create book item
            $bookItem = BookItem::create($bookItemData);
            
            // Create book with reference to book item
            $bookData['book_item_id'] = $bookItem->id;
            $book = Book::create($bookData);
            
            // Load the book item relationship for the response
            $book->load('bookItem');
            
            DB::commit();
            
            return new BookResource($book);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create book', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Book $book)
    {
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        if (!empty($includes)) {
            // Always include bookItem
            if (!in_array('bookItem', $includes)) {
                $includes[] = 'bookItem';
            }
            
            $book->loadMissing($includes);
        } else {
            // Default to always include bookItem
            $book->load('bookItem');
        }
        
        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\Book\UpdateBookRequest  $request
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            // Extract book-specific fields
            $bookData = array_intersect_key($validated, [
                'edition' => '',
                'pages' => '',
                'cover_type' => '',
                'dimensions' => '',
                'weight_grams' => '',
                'barcode' => '',
                'shelf_location_detail' => '',
                'reference_only' => '',
            ]);
            
            // Extract book item fields - remove book-specific fields
            $bookItemData = array_diff_key($validated, [
                'edition' => '',
                'pages' => '',
                'cover_type' => '',
                'dimensions' => '',
                'weight_grams' => '',
                'barcode' => '',
                'shelf_location_detail' => '',
                'reference_only' => '',
            ]);
            
            // Update book
            if (!empty($bookData)) {
                $book->update($bookData);
            }
            
            // Update book item if we have book item data
            if (!empty($bookItemData) && $book->bookItem) {
                $book->bookItem->update($bookItemData);
            }
            
            // Reload book with book item
            $book->load('bookItem');
            
            DB::commit();
            
            return new BookResource($book);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update book', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy(Book $book)
    {
        try {
            DB::beginTransaction();
            
            // Store book item ID before deleting the book
            $bookItemId = $book->book_item_id;
            
            // Delete the book
            $book->delete();
            
            // Delete the associated book item
            if ($bookItemId) {
                $bookItem = BookItem::find($bookItemId);
                if ($bookItem) {
                    $bookItem->delete();
                }
            }
            
            DB::commit();
            
            return response()->json(['message' => 'Book deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete book', 'error' => $e->getMessage()], 500);
        }
    }
}