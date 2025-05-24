<?php

namespace App\Http\Controllers\Book;

use App\Http\Controllers\Controller;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Http\Resources\Book\BookCollection;
use App\Http\Resources\Book\BookResource;
use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Book::query();
        
        // Apply filters if provided
        if ($request->has('title')) {
            $query->whereHas('bookItem', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->title . '%');
            });
        }
        
        if ($request->has('isbn')) {
            $query->where('isbn', 'like', '%' . $request->isbn . '%');
        }
        
        if ($request->has('is_borrowable')) {
            $query->where('is_borrowable', $request->boolean('is_borrowable'));
        }
        
        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['bookItem', 'bookCondition', 'shelf', 'library'];
            $query->with(array_intersect($relationships, $allowedRelations));
            
            // Special case for bookItem.category or other nested relations
            if (in_array('bookItem.category', $relationships)) {
                $query->with('bookItem.category');
            }
            
            if (in_array('bookItem.language', $relationships)) {
                $query->with('bookItem.language');
            }
        }
        
        // Paginate the results
        $perPage = $request->per_page ?? 15;
        $books = $query->paginate($perPage);
        
        return new BookCollection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            // Extract BookItem data
            $bookItemData = [
                'title' => $validated['title'],
                'author' => $validated['author'],
                'description' => $validated['description'] ?? null,
                'cover_image_url' => $validated['cover_image_url'] ?? null,
                'language_id' => $validated['language_id'],
                'category_id' => $validated['category_id'],
                'grade' => $validated['grade'] ?? null,
                'library_id' => $validated['library_id'],
                'shelf_id' => $validated['shelf_id'] ?? null,
                'subject_id' => $validated['subject_id'] ?? null,
            ];
            
            // Create BookItem first
            $bookItem = BookItem::create($bookItemData);
            
            // Extract Book specific data
            $bookData = [
                'edition' => $validated['edition'] ?? null,
                'isbn' => $validated['isbn'],
                'title' => $validated['title'], // Duplicating title for direct queries
                'pages' => $validated['pages'] ?? null,
                'is_borrowable' => $validated['is_borrowable'] ?? true,
                'book_item_id' => $bookItem->id,
                'shelf_id' => $validated['shelf_id'] ?? null,
                'library_id' => $validated['library_id'],
            ];
            
            // Create Book
            $book = Book::create($bookData);
            
            // Create book condition if provided
            if (isset($validated['condition'])) {
                BookCondition::create([
                    'book_id' => $book->id,
                    'condition' => $validated['condition'],
                    'note' => $validated['condition_note'] ?? null,
                ]);
            }
            
            // Load relationships for the response
            $book->load(['bookItem', 'bookCondition']);
            
            DB::commit();
            
            return new BookResource($book);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to create book',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Book $book)
    {
        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['bookItem', 'bookCondition', 'shelf', 'library'];
            $book->load(array_intersect($relationships, $allowedRelations));
            
            // Handle nested relations
            if (in_array('bookItem.category', $relationships)) {
                $book->load('bookItem.category');
            }
            
            if (in_array('bookItem.language', $relationships)) {
                $book->load('bookItem.language');
            }
        }
        
        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();
        
        // Update the book
        $book->update($validated);
        
        // Update book condition if provided
        if (isset($validated['condition'])) {
            BookCondition::updateOrCreate(
                ['book_id' => $book->id],
                [
                    'condition' => $validated['condition'],
                    'note' => $validated['condition_note'] ?? null,
                ]
            );
        }
        
        // Load relationships for the response
        $book->load(['bookItem', 'bookCondition']);
        
        return new BookResource($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        try {
            DB::beginTransaction();
            
            // Check if there are any loans or reservations for this book
            if ($book->reservations()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete this book because it has associated reservations.'
                ], Response::HTTP_CONFLICT);
            }
            
            // Delete book condition first
            if ($book->bookCondition) {
                $book->bookCondition->delete();
            }
            
            // Delete the book
            $book->delete();
            
            DB::commit();
            
            return response()->json(['message' => 'Book deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to delete book',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
