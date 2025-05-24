<?php

namespace App\Http\Controllers\EBook;

use App\Http\Controllers\Controller;
use App\Http\Requests\EBook\StoreEBookRequest;
use App\Http\Requests\EBook\UpdateEBookRequest;
use App\Http\Resources\EBook\EBookCollection;
use App\Http\Resources\EBook\EBookResource;
use App\Models\BookItem;
use App\Models\EBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class EBookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EBook::query();
        
        // Apply filters if provided
        if ($request->has('file_format')) {
            $query->where('file_format', $request->file_format);
        }
        
        if ($request->has('is_downloadable')) {
            $query->where('is_downloadable', $request->boolean('is_downloadable'));
        }
        
        if ($request->has('e_book_type_id')) {
            $query->where('e_book_type_id', $request->e_book_type_id);
        }
        
        if ($request->has('title')) {
            $query->whereHas('bookItem', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->title . '%');
            });
        }
        
        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['bookItem', 'ebookType', 'bookmarks', 'notes', 'collections'];
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
        $ebooks = $query->paginate($perPage);
        
        return new EBookCollection($ebooks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEBookRequest $request)
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
                'subject_id' => $validated['subject_id'] ?? null,
            ];
            
            // Create BookItem first
            $bookItem = BookItem::create($bookItemData);
            
            // Extract EBook specific data
            $ebookData = [
                'book_item_id' => $bookItem->id,
                'file_path' => $validated['file_path'],
                'file_format' => $validated['file_format'] ?? null,
                'file_name' => $validated['file_name'] ?? null,
                'isbn' => $validated['isbn'] ?? null,
                'file_size_mb' => $validated['file_size_mb'] ?? null,
                'pages' => $validated['pages'] ?? null,
                'is_downloadable' => $validated['is_downloadable'] ?? true,
                'e_book_type_id' => $validated['e_book_type_id'],
            ];
            
            // Create EBook
            $ebook = EBook::create($ebookData);
            
            // Load relationships for the response
            $ebook->load(['bookItem', 'ebookType']);
            
            DB::commit();
            
            return new EBookResource($ebook);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to create ebook',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, EBook $ebook)
    {
        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['bookItem', 'ebookType', 'bookmarks', 'notes', 'collections'];
            $ebook->load(array_intersect($relationships, $allowedRelations));
            
            // Handle nested relations
            if (in_array('bookItem.category', $relationships)) {
                $ebook->load('bookItem.category');
            }
            
            if (in_array('bookItem.language', $relationships)) {
                $ebook->load('bookItem.language');
            }
        }
        
        return new EBookResource($ebook);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEBookRequest $request, EBook $ebook)
    {
        $validated = $request->validated();
        
        // Update the ebook
        $ebook->update($validated);
        
        // Load relationships for the response
        $ebook->load(['bookItem', 'ebookType']);
        
        return new EBookResource($ebook);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EBook $ebook)
    {
        try {
            DB::beginTransaction();
            
            // Check if there are any references to this ebook
            if ($ebook->bookmarks()->count() > 0 || 
                $ebook->notes()->count() > 0 || 
                $ebook->collections()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete this ebook because it has associated bookmarks, notes, or is part of collections.'
                ], Response::HTTP_CONFLICT);
            }
            
            // Delete the ebook
            $ebook->delete();
            
            DB::commit();
            
            return response()->json(['message' => 'EBook deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to delete ebook',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
