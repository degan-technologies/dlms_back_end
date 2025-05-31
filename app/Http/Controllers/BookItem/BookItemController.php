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
use Illuminate\Support\Facades\Storage;

class BookItemController extends Controller {
    public function index(Request $request) {
        $query = BookItem::query();

        // 1. Search by title and author
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }

        if ($request->filled('author')) {
            $query->where('author', 'like', '%' . $request->input('author') . '%');
        }


        $idFilters = ['category_id', 'language_id', 'subject_id', 'grade_id', 'library_id', 'user_id'];
        foreach ($idFilters as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }        // 3. Filter by format (book, ebook, all, or metadata_only)
        $format = $request->input('format', 'metadata_only');

        if ($format === 'book') {
            // Find BookItems that have physical books
            $query->whereHas('books');
        } elseif ($format === 'ebook') {
            // Find BookItems that have ebooks
            $query->whereHas('ebooks');
        } elseif ($format === 'all') {
            // Find BookItems that have either books or ebooks
            $query->where(function ($q) {
                $q->whereHas('books')->orWhereHas('ebooks');
            });
        } elseif ($format === 'metadata_only') {
            // Just return BookItems without requiring books or ebooks
            // No additional where clause needed
        } // Always load these base relationships
        $relationships = ['language', 'category', 'subject', 'grade'];

        // Add user-requested additional relationships
        if ($request->has('with')) {
            $requestedWith = explode(',', $request->with);
            foreach (['library', 'user'] as $validRelation) {
                if (in_array($validRelation, $requestedWith)) {
                    $relationships[] = $validRelation;
                }
            }
        }

        // For ebooks, always load teacher information
        if ($format === 'ebook' || $format === 'all') {
            $relationships[] = 'user.staff:id,user_id,first_name,last_name,department';
        }

        // Load relationships based on format type
        if ($format === 'book' || $format === 'all') {
            // For books, we only need counts, not the actual book data
            $query->withCount('books'); // Total books count
            $query->withCount([
                'books as available_books_count' => function ($q) {
                    $q->where('is_borrowable', true)->where('is_reserved', false);
                }
            ]);
        }

        if ($format === 'ebook' || $format === 'all') {
            // For ebooks, we only need counts, not the actual ebook data
            $query->withCount('ebooks'); // Total ebooks count
            $query->withCount([
                'ebooks as downloadable_ebooks_count' => function ($q) {
                    $q->where('is_downloadable', true);
                }
            ]);

            // Count ebooks by type (PDF, AUDIO, VIDEO)
            $query->withCount([
                'ebooks as pdf_ebooks_count' => function ($q) {
                    $q->whereHas('ebookType', function ($q2) {
                        $q2->where('name', 'PDF');
                    });
                },
                'ebooks as audio_ebooks_count' => function ($q) {
                    $q->whereHas('ebookType', function ($q2) {
                        $q2->where('name', 'AUDIO');
                    });
                },
                'ebooks as video_ebooks_count' => function ($q) {
                    $q->whereHas('ebookType', function ($q2) {
                        $q2->where('name', 'VIDEO');
                    });
                }
            ]);
        }
        // Skip loading books/ebooks relationships if metadata_only is specified
        if ($format !== 'metadata_only') {
            // Don't load books and ebooks contents, only metadata and counts
            $baseRelationshipsOnly = array_filter($relationships, function ($rel) {
                return !str_starts_with($rel, 'books.') && !str_starts_with($rel, 'ebooks.') && $rel !== 'books' && $rel !== 'ebooks';
            });
            $query->with($baseRelationshipsOnly);
        } else {
            // For metadata_only, just load the base relationships
            $baseRelationships = array_filter($relationships, function ($rel) {
                return !str_starts_with($rel, 'books.') && !str_starts_with($rel, 'ebooks.');
            });
            $query->with($baseRelationships);
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $bookItems = $query->paginate($perPage);

        return new BookItemCollection($bookItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookItemRequest $request) {
        $user = auth()->user();
        $validated = $request->validated();

        // Handle cover image upload if present
        if (isset($validated['cover_image']) && $validated['cover_image']) {
            $coverImage = $validated['cover_image'];
            $path = Storage::disk('public')->put('cover_images', $coverImage);
            $validated['cover_image'] = $path;
        }

        $bookItem = $user->bookItems()->create($validated);

        return new BookItemResource($bookItem);
    }
    /**
     * Display the specified resource.
     */
    public function show(Request $request, BookItem $bookItem) {
        // Format preference
        $preferEbook = $request->has('format') && $request->format === 'ebook';        // Load the appropriate relationships based on preference
        if ($preferEbook) {
            // Priority on ebooks - include notes, chat messages, and bookmark for current user
            $userId = $request->user() ? $request->user()->id : null;
            $bookItem->load([
                'ebooks' => function ($q) use ($userId) {
                    $q->with([
                        'ebookType',
                        'notes' => function ($nq) {
                            $nq->with('user:id,username');
                        },
                        'chatMessages' => function ($cq) {
                            $cq->with('user:id,username');
                        },
                        'bookmark' => function ($bq) use ($userId) {
                            $bq->where('user_id', $userId)->with('user:id,username');
                        }
                    ]);
                }
            ]);
        } else {
            // Priority on physical books
            $bookItem->load(['books' => function ($q) {
                $q->with('shelf'); // Load shelf relationship for each book
            }]);

            // Only load ebooks if no books available
            if ($bookItem->books->isEmpty()) {
                $bookItem->load([
                    'ebooks' => function ($q) {
                        $q->with('ebookType');
                    }
                ]);
            }
        } // Always load common metadata relationships
        $bookItem->load(['language', 'category', 'subject', 'grade']);

        // Include additional relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $additionalRelations = array_intersect($relationships, ['library']);
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
     * Remove multiple book items from storage.
     */
    public function destroyMultiple(Request $request) {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'No book item IDs provided.'], Response::HTTP_BAD_REQUEST);
        }
        $failed = [];
        foreach ($ids as $id) {
            $bookItem = BookItem::find($id);
            if (!$bookItem) {
                $failed[] = [
                    'id' => $id,
                    'reason' => 'Book item not found.'
                ];
                continue;
            }
            if ($bookItem->books()->count() > 0 || $bookItem->ebooks()->count() > 0) {
                $failed[] = [
                    'id' => $id,
                    'reason' => 'Book item has associated books or ebooks.'
                ];
                continue;
            }
            $bookItem->delete();
        }
        if (!empty($failed)) {
            return response()->json([
                'message' => 'Some book items could not be deleted.',
                'failed' => $failed
            ], Response::HTTP_CONFLICT);
        }
        return response()->json(['message' => 'Book items deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Get the 5 most recently added book items (new arrivals).
     */    public function newArrivals(Request $request) {
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

        // Always load both books and ebooks with complete information
        $query->with(['books' => function ($q) {
            $q->with('shelf'); // Load shelf relationship for each book
        }]);
        $query->with(['ebooks' => function ($q) {
            $q->with('ebookType');
        }]);

        // Always load common metadata relationships
        $query->with(['language', 'category', 'subject', 'grade']);

        // Include additional relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $additionalRelations = array_intersect($relationships, ['library']);
            if (!empty($additionalRelations)) {
                $query->with($additionalRelations);
            }
        }

        // Order by creation date (newest first) and limit to 5
        $bookItems = $query->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return new BookItemCollection($bookItems);
    }

    /**
     * Get featured or recommended book items (top 5 based on a criteria).
     */    public function featured(Request $request) {
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

        // Always load both books and ebooks with complete information
        $query->with(['books' => function ($q) {
            $q->with('shelf'); // Load shelf relationship for each book
        }]);
        $query->with(['ebooks' => function ($q) {
            $q->with('ebookType');
        }]);

        // Always load common metadata relationships
        $query->with(['language', 'category', 'subject', 'grade']);

        // Include additional relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $additionalRelations = array_intersect($relationships, ['library']);
            if (!empty($additionalRelations)) {
                $query->with($additionalRelations);
            }
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
     * Display a single ebook with its notes and chat messages
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

    /**
     * Advanced search for BookItems, returning both digital and physical formats distinctly.
     * This method allows searching by title, author, description, and filtering by various attributes.
     */
    public function search(Request $request) {
        $query = BookItem::query();

        // Keyword search (title, author, description, etc.)
        if ($request->filled('q')) {
            $keyword = $request->input('q');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%$keyword%")
                    ->orWhere('author', 'like', "%$keyword%")
                    ->orWhere('description', 'like', "%$keyword%")
                    ->orWhereHas('subject', function ($sq) use ($keyword) {
                        $sq->where('name', 'like', "%$keyword%")
                            ->orWhere('description', 'like', "%$keyword%");
                    });
            });
        }

        // Apply filters (category, language, subject, grade, library, user)
        $idFilters = ['category_id', 'language_id', 'subject_id', 'grade_id', 'library_id', 'user_id'];
        foreach ($idFilters as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        // Always eager load relationships
        $query->with(['language', 'category', 'subject', 'grade', 'library', 'user']);
        $query->with(['books' => function ($q) {
            $q->with('shelf');
        }]);
        $query->with(['ebooks' => function ($q) {
            $q->with('ebookType');
        }]);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $bookItems = $query->paginate($perPage);

        // Transform results: for each BookItem, split into digital and physical if both exist
        $results = [];
        foreach ($bookItems as $item) {
            if ($item->books->count() > 0) {
                $physical = [
                    'format' => 'physical',
                    'book_item' => new \App\Http\Resources\BookItem\BookItemResource($item),
                    'books' => $item->books,
                ];
                $results[] = $physical;
            }
            if ($item->ebooks->count() > 0) {
                $digital = [
                    'format' => 'digital',
                    'book_item' => new \App\Http\Resources\BookItem\BookItemResource($item),
                    'ebooks' => $item->ebooks,
                ];
                $results[] = $digital;
            }
        }

        return response()->json([
            'data' => $results,
            'meta' => [
                'current_page' => $bookItems->currentPage(),
                'last_page' => $bookItems->lastPage(),
                'per_page' => $bookItems->perPage(),
                'total' => $bookItems->total(),
            ]
        ]);
    }

    public function teacherBookItems(Request $request) {
        $user = $request->user();

        // Query all BookItems using the user's bookItems relationship (no filter on ebooks)
        $query = $user->bookItems();

        // Search by title and author
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }
        if ($request->filled('author')) {
            $query->where('author', 'like', '%' . $request->input('author') . '%');
        }

        // Filter by IDs
        $idFilters = ['category_id', 'language_id', 'subject_id', 'grade_id', 'library_id'];
        foreach ($idFilters as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        // Relationships to load
        $relationships = ['language', 'category', 'subject', 'grade'];

        // Add user-requested additional relationships
        if ($request->has('with')) {
            $requestedWith = explode(',', $request->with);
            foreach (['library'] as $validRelation) {
                if (in_array($validRelation, $requestedWith)) {
                    $relationships[] = $validRelation;
                }
            }
        }

        // Always load teacher info for ebooks
        $relationships[] = 'user.staff:id,user_id,first_name,last_name,department';

        // Only need counts for ebooks, not actual ebook data
        $query->withCount('ebooks');
        $query->withCount([
            'ebooks as downloadable_ebooks_count' => function ($q) {
                $q->where('is_downloadable', true);
            }
        ]);
        $query->withCount([
            'ebooks as pdf_ebooks_count' => function ($q) {
                $q->whereHas('ebookType', function ($q2) {
                    $q2->where('name', 'PDF');
                });
            },
            'ebooks as video_ebooks_count' => function ($q) {
                $q->whereHas('ebookType', function ($q2) {
                    $q2->where('name', 'VIDEO');
                });
            }
        ]);

        // Don't load books/ebooks contents, only metadata and counts
        $baseRelationshipsOnly = array_filter($relationships, function ($rel) {
            return !str_starts_with($rel, 'books.') && !str_starts_with($rel, 'ebooks.') && $rel !== 'books' && $rel !== 'ebooks';
        });
        $query->with($baseRelationshipsOnly);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $bookItems = $query->paginate($perPage);
        return new BookItemCollection($bookItems);
    }
}
