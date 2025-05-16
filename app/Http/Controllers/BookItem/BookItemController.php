<?php

namespace App\Http\Controllers\BookItem;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookItem\StoreBookItemRequest;
use App\Http\Requests\BookItem\UpdateBookItemRequest;
use App\Http\Resources\BookItem\BookItemCollection;
use App\Http\Resources\BookItem\BookItemResource;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookItemController extends Controller {
    /**
     * Display a listing of the resource.
    */
    public function index(Request $request)
    {
       $query = BookItem::query();
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('author')) {
            $query->where('author', 'like', '%' . $request->author . '%');
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('language_id')) {
            $query->where('language_id', $request->language_id);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Add grade filter
        if ($request->has('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        if ($request->has('ebook_type_id')) {
            $query->whereHas('ebooks', function ($q) use ($request) {
                $q->where('e_book_type_id', $request->ebook_type_id);
            });
        }

        // Format preference filter (book or ebook)
        $preferEbook = $request->has('format') && $request->format === 'ebook';

        // Filter by item type if specified
        if ($request->has('item_type')) {
            if ($request->item_type === 'book') {
                $query->whereHas('books');
                if ($request->has('is_borrowable')) {
                    $query->whereHas('books', function ($q) use ($request) {
                        $q->where('is_borrowable', filter_var($request->is_borrowable, FILTER_VALIDATE_BOOLEAN));
                    });
                }
            } else if ($request->item_type === 'ebook') {
                $query->whereHas('ebooks');
            }
        }

        // Load relationships based on preference
        if ($preferEbook) {
            // Prioritize ebooks 
            $query->with(['ebooks' => function ($q) {
                $q->with('ebookType');
                $q->select('id', 'book_item_id', 'file_path', 'file_format', 'file_name', 'e_book_type_id', 'is_downloadable');
            }]);

            // Only load books if specifically requested in 'with'
            if ($request->has('with') && in_array('books', explode(',', $request->with))) {
                $query->with(['books' => function ($q) {
                    $q->select('id', 'book_item_id', 'is_borrowable', 'shelf_id', 'library_id');
                }]);
            }
        } else {
            // Default: load books first, then ebooks
            $query->with(['books' => function ($q) {
                $q->select('id', 'book_item_id', 'is_borrowable', 'shelf_id', 'library_id');
            }]);

            // Only load ebooks if specifically requested or if no books available
            $query->with(['ebooks' => function ($q) {
                $q->with('ebookType');
                $q->select('id', 'book_item_id', 'file_path', 'file_format', 'file_name', 'e_book_type_id', 'is_downloadable');
            }]);
        }

        // Count of available physical books
        $query->withCount([
            'books as available_books_count' => function ($query) {
                $query->where('is_borrowable', true);
            }
        ]);

        // Include additional relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $additionalRelations = array_intersect($relationships, ['books', 'language', 'category', 'library', 'subject']);
            if (!empty($additionalRelations)) {
                $query->with($additionalRelations);
            }
        }

        // Paginate the results
        $perPage = $request->per_page ?? 15;
        $bookItems = $query->paginate($perPage);
        return $bookItems;

        return new BookItemCollection($bookItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookItemRequest $request) {
        $validated = $request->validated();

        $bookItem = BookItem::create($validated);

        return new BookItemResource($bookItem);
    }
    /**
     * Display the specified resource.
     */
    public function show(Request $request, BookItem $bookItem) {
        // Format preference
        $preferEbook = $request->has('format') && $request->format === 'ebook';

        // Load the appropriate relationships based on preference
        if ($preferEbook) {
            // Priority on ebooks
            $bookItem->load([
                'ebooks' => function ($q) {
                    $q->with('ebookType');
                    $q->select('id', 'book_item_id', 'file_path', 'file_format', 'file_name', 'e_book_type_id', 'is_downloadable');
                }
            ]);
        } else {
            // Priority on physical books
            $bookItem->load([
                'books' => function ($q) {
                    $q->select('id', 'book_item_id', 'is_borrowable', 'shelf_id', 'library_id');
                }
            ]);

            // Only load ebooks if no books available
            if ($bookItem->books->isEmpty()) {
                $bookItem->load([
                    'ebooks' => function ($q) {
                        $q->with('ebookType');
                        $q->select('id', 'book_item_id', 'file_path', 'file_format', 'file_name', 'e_book_type_id', 'is_downloadable');
                    }
                ]);
            }
        }

        // Include additional relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $additionalRelations = array_intersect($relationships, ['language', 'category', 'library', 'subject']);
            if (!empty($additionalRelations)) {
                $bookItem->load($additionalRelations);
            }
        }

        // Load the available books count for physical books
        if (!$preferEbook) {
            $bookItem->loadCount(['books as available_books_count' => function ($query) {
                $query->where('is_borrowable', true);
            }]);
        }

        return new BookItemResource($bookItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookItemRequest $request, BookItem $bookItem) {
        $validated = $request->validated();

        $bookItem->update($validated);

        return new BookItemResource($bookItem);
    }

    /**
     * Remove the specified resource from storage.
     */    public function destroy(BookItem $bookItem) {
        // First check if there are any books or ebooks associated with this item
        if ($bookItem->books()->count() > 0 || $bookItem->ebooks()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete this book item because it has associated books or ebooks.'
            ], Response::HTTP_CONFLICT);
        }

        $bookItem->delete();

        return response()->json(['message' => 'Book item deleted successfully']);
    }

    /**
     * Get the 5 most recently added book items (new arrivals).
     */
    public function newArrivals(Request $request) {
        $query = BookItem::query();

        // Apply category filter if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Apply library filter if provided
        if ($request->has('library_id')) {
            $query->where('library_id', $request->library_id);
        }

        // Apply subject filter if provided
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $query->with(array_intersect($relationships, ['books', 'ebooks', 'language', 'category', 'library', 'subject']));
        }

        // Order by creation date (newest first) and limit to 5
        $bookItems = $query->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return new BookItemCollection($bookItems);
    }

    /**
     * Get featured or recommended book items (top 5 based on a criteria).
     */
    public function featured(Request $request) {
        $query = BookItem::query();

        // Apply category filter if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Apply library filter if provided
        if ($request->has('library_id')) {
            $query->where('library_id', $request->library_id);
        }

        // You might want to implement some criteria for "featured" items
        // For example, items that are marked as featured, or have the most views, etc.
        // This is a placeholder implementation that just returns 5 random items

        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $query->with(array_intersect($relationships, ['books', 'ebooks', 'language', 'category', 'library', 'subject']));
        }

        // For demonstration, returning 5 random items
        // In a real implementation, you might use a 'is_featured' flag or sort by popularity
        $bookItems = $query->inRandomOrder()
            ->take(5)
            ->get();

        return new BookItemCollection($bookItems);
    }

    /**
     * Get only physical books
     */
    public function physicalBooks(Request $request) {
        $request->merge(['item_type' => 'book']);
        return $this->index($request);
    }

    /**
     * Get only ebooks
     */
    public function ebooks(Request $request) {
        $request->merge(['item_type' => 'ebook', 'format' => 'ebook']);
        return $this->index($request);
    }

    /**
     * Display a single physical book
     */
    public function showPhysicalBook(Request $request, BookItem $bookItem) {
        // Make sure we only return physical book data
        $request->merge(['format' => 'book']);

        // Check if this BookItem has physical books
        if (!$bookItem->books()->exists()) {
            return response()->json([
                'message' => 'This item is not available as a physical book'
            ], 404);
        }

        return $this->show($request, $bookItem);
    }

    /**
     * Display a single ebook
     */
    public function showEbook(Request $request, BookItem $bookItem) {
        // Make sure we only return ebook data
        $request->merge(['format' => 'ebook']);

        // Check if this BookItem has ebooks
        if (!$bookItem->ebooks()->exists()) {
            return response()->json([
                'message' => 'This item is not available as an ebook'
            ], 404);
        }

        return $this->show($request, $bookItem);
    }
}
