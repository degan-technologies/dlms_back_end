<?php

namespace App\Http\Controllers\Book;

use App\Http\Controllers\Controller;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Http\Resources\Book\BookCollection;
use App\Http\Resources\Book\BookResource;
use App\Http\Resources\BookItem\BookItemResource;
use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        $query = Book::query();

        // Apply filters if provided
        if ($request->has('title')) {
            $query->whereHas('bookItem', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->title . '%');
            });
        }
        if ($request->has('book_item_id')) {
            $query->where('book_item_id', $request->book_item_id);
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
    public function store(StoreBookRequest $request) {
        $user = auth()->user();
        $validated = $request->validated();
        if (isset($validated['is_borrowable'])) {
            $validated['is_borrowable'] = filter_var($validated['is_borrowable'], FILTER_VALIDATE_BOOLEAN);
        }
        try {

            if (isset($validated['cover_image']) && $validated['cover_image']) {
                $coverImage = $validated['cover_image'];
                $path = Storage::disk('public')->put('cover_images', $coverImage);
                $validated['cover_image'] = $path;
            }
            $book = $user->books()->create($validated);

            $book->load(['bookItem']);

            return (new BookResource($book))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create book',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Book $book) {
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
    public function update(UpdateBookRequest $request, Book $book) {
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
    public function destroy(Book $book) {
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
