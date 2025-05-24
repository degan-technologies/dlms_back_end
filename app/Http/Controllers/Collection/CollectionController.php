<?php

namespace App\Http\Controllers\Collection;

use App\Http\Controllers\Controller;
use App\Http\Requests\Collection\StoreCollectionRequest;
use App\Http\Requests\Collection\UpdateCollectionRequest;
use App\Http\Resources\Collection\CollectionCollection;
use App\Http\Resources\Collection\CollectionResource;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller {
    /**
     * Display a listing of all collections (not user-specific).
     */
    public function index(Request $request) {
        $query = Collection::query();
        // Exclude collections created by users with the 'student' role
        $query->whereDoesntHave('user.roles', function ($q) {
            $q->where('name', 'student');
        });

        // Search by name
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter by collections containing specific ebooks
        if ($request->has('contains_ebook_id')) {
            $query->whereHas('ebooks', function ($q) use ($request) {
                $q->where('e_books.id', $request->contains_ebook_id);
            });
        }

        // Filter by collections containing any of the specified ebooks
        if ($request->has('contains_any_ebook_ids')) {
            $ebookIds = explode(',', $request->contains_any_ebook_ids);
            $query->whereHas('ebooks', function ($q) use ($ebookIds) {
                $q->whereIn('e_books.id', $ebookIds);
            });
        }

        // Filter by collections containing all of the specified ebooks
        if ($request->has('contains_all_ebook_ids')) {
            $ebookIds = explode(',', $request->contains_all_ebook_ids);
            foreach ($ebookIds as $ebookId) {
                $query->whereHas('ebooks', function ($q) use ($ebookId) {
                    $q->where('e_books.id', $ebookId);
                });
            }
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['ebooks', 'user'];
            $query->with(array_intersect($relationships, $allowedRelations));

            // Special case for nested relationships
            if (in_array('ebooks.bookItem', $relationships)) {
                $query->with('ebooks.bookItem');
            }
        }

        // Custom sorting
        if ($request->has('sort_by') && in_array($request->sort_by, ['created_at', 'updated_at', 'name'])) {
            $direction = $request->has('sort_direction') && $request->sort_direction === 'asc' ? 'asc' : 'desc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->latest(); // Default sort by created_at desc
        }

        // Sort by ebook count
        if ($request->has('sort_by_ebook_count')) {
            $direction = $request->has('sort_direction') && $request->sort_direction === 'asc' ? 'asc' : 'desc';
            $query->withCount('ebooks')->orderBy('ebooks_count', $direction);
        }

        // Paginate the results
        $perPage = $request->per_page ?? 15;
        $collections = $query->paginate($perPage);

        return new CollectionCollection($collections);
    }

    /**
     * Display a listing of the authenticated user's collections.
     */
    public function myCollections(Request $request) {
        $query = Collection::query();
        $query->where('user_id', auth()->id());

        // Search by name
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter by collections containing specific ebooks
        if ($request->has('contains_ebook_id')) {
            $query->whereHas('ebooks', function ($q) use ($request) {
                $q->where('e_books.id', $request->contains_ebook_id);
            });
        }

        // Filter by collections containing any of the specified ebooks
        if ($request->has('contains_any_ebook_ids')) {
            $ebookIds = explode(',', $request->contains_any_ebook_ids);
            $query->whereHas('ebooks', function ($q) use ($ebookIds) {
                $q->whereIn('e_books.id', $ebookIds);
            });
        }

        // Filter by collections containing all of the specified ebooks
        if ($request->has('contains_all_ebook_ids')) {
            $ebookIds = explode(',', $request->contains_all_ebook_ids);
            foreach ($ebookIds as $ebookId) {
                $query->whereHas('ebooks', function ($q) use ($ebookId) {
                    $q->where('e_books.id', $ebookId);
                });
            }
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['ebooks', 'user'];
            $query->with(array_intersect($relationships, $allowedRelations));

            // Special case for nested relationships
            if (in_array('ebooks.bookItem', $relationships)) {
                $query->with('ebooks.bookItem');
            }
        }

        // Custom sorting
        if ($request->has('sort_by') && in_array($request->sort_by, ['created_at', 'updated_at', 'name'])) {
            $direction = $request->has('sort_direction') && $request->sort_direction === 'asc' ? 'asc' : 'desc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->latest(); // Default sort by created_at desc
        }

        // Sort by ebook count
        if ($request->has('sort_by_ebook_count')) {
            $direction = $request->has('sort_direction') && $request->sort_direction === 'asc' ? 'asc' : 'desc';
            $query->withCount('ebooks')->orderBy('ebooks_count', $direction);
        }

        // Paginate the results
        $perPage = $request->per_page ?? 15;
        $collections = $query->paginate($perPage);

        return new CollectionCollection($collections);
    }

    /**
     * Store a newly created collection in storage.
     */
    public function store(StoreCollectionRequest $request) {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Add the authenticated user's ID
            $validated['user_id'] = auth()->id();

            // Extract e_book_ids if present
            $ebookIds = $validated['e_book_ids'] ?? [];
            unset($validated['e_book_ids']);

            // Create the collection
            $collection = Collection::create($validated);

            // Attach ebooks if any
            if (!empty($ebookIds)) {
                $collection->ebooks()->attach($ebookIds);
            }

            // Load relationships for the response
            $collection->load('ebooks.bookItem');

            DB::commit();

            return new CollectionResource($collection);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create collection',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified collection.
     */
    public function show(Request $request, Collection $collection) {

        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['ebooks', 'user'];
            $collection->load(array_intersect($relationships, $allowedRelations));

            // Special case for nested relationships
            if (in_array('ebooks.bookItem', $relationships)) {
                $collection->load('ebooks.bookItem');
            }
        }

        return new CollectionResource($collection);
    }

    /**
     * Display the specified collection for the authenticated user only.
     */
    public function myCollectionShow(Request $request, Collection $collection) {
        // Check if the collection belongs to the authenticated user
        if ($collection->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['ebooks', 'user'];
            $collection->load(array_intersect($relationships, $allowedRelations));
            // Special case for nested relationships
            if (in_array('ebooks.bookItem', $relationships)) {
                $collection->load('ebooks.bookItem');
            }
        }
        return new CollectionResource($collection);
    }

    /**
     * Update the specified collection in storage.
     */
    public function update(UpdateCollectionRequest $request, Collection $collection) {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Extract e_book_ids if present
            $ebookIds = $validated['e_book_ids'] ?? null;
            unset($validated['e_book_ids']);

            // Update the collection
            $collection->update($validated);

            // Sync ebooks if provided
            if (isset($ebookIds)) {
                $collection->ebooks()->sync($ebookIds);
            }

            // Load relationships for the response
            $collection->load('ebooks.bookItem');

            DB::commit();

            return new CollectionResource($collection);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update collection',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add an ebook to the collection.
     */
    public function addEbook(Request $request, Collection $collection) {
        // Check if the collection belongs to the authenticated user
        if ($collection->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'e_book_id' => 'required|exists:e_books,id'
        ]);

        // Check if the ebook is already in the collection
        if ($collection->ebooks()->where('e_books.id', $request->e_book_id)->exists()) {
            return response()->json(['message' => 'EBook is already in this collection'], Response::HTTP_BAD_REQUEST);
        }

        // Attach the ebook to the collection
        $collection->ebooks()->attach($request->e_book_id);

        // Load relationships for the response
        $collection->load('ebooks.bookItem');

        return new CollectionResource($collection);
    }

    /**
     * Remove an ebook from the collection.
     */
    public function removeEbook(Request $request, Collection $collection) {
        // Check if the collection belongs to the authenticated user
        if ($collection->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'e_book_id' => 'required|exists:e_books,id'
        ]);

        // Detach the ebook from the collection
        $collection->ebooks()->detach($request->e_book_id);

        // Load relationships for the response
        $collection->load('ebooks.bookItem');

        return new CollectionResource($collection);
    }

    /**
     * Remove the specified collection from storage.
     */
    public function destroy(Collection $collection) {
        // Check if the collection belongs to the authenticated user
        if ($collection->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        // Detach all ebooks first (automatically handled by the many-to-many relationship)
        $collection->delete();

        return response()->json(['message' => 'Collection deleted successfully']);
    }
}
