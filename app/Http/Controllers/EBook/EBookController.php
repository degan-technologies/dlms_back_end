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
use Illuminate\Support\Facades\Storage;

class EBookController extends Controller {
    /**
     * Display a listing of the resource.
     */    public function index(Request $request) {
        $query = EBook::query();

        // Always load required relationships for EBook resource
        $query->with(['bookmarks', 'notes', 'chatMessages', 'collections', 'ebookType']);
        
        // Also include interaction counts
        $query->withCount(['bookmarks', 'notes', 'chatMessages', 'collections']);

        if ($request->has('book_item_id')) {
            $query->where('book_item_id', $request->book_item_id);
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

        // Include additional relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['bookItem', 'ebookType', 'bookmarks', 'notes', 'collections'];
            $additionalRelations = array_diff(array_intersect($relationships, $allowedRelations), ['bookmarks', 'notes', 'chatMessages', 'collections', 'ebookType']);
            
            if (!empty($additionalRelations)) {
                $query->with($additionalRelations);
            }

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
    public function store(StoreEBookRequest $request) {
        $user = auth()->user();
        $validated = $request->validated();
        if (isset($validated['is_downloadable'])) {
            $validated['is_downloadable'] = filter_var($validated['is_downloadable'], FILTER_VALIDATE_BOOLEAN);
        }
        try {
            // Handle file upload and get file size/pages
            if (isset($validated['pdf_file'])) {
                $eBook = $validated['pdf_file'];
                $path = Storage::disk('public')->put('ebooks', $eBook);
                $validated['file_path'] = $path;

                // Get file size in MB
                $fileSizeBytes = Storage::disk('public')->size($path);
                $validated['file_size_mb'] = round($fileSizeBytes / 1048576, 2);

                // Get number of pages using Smalot\PdfParser
                try {
                    $pdfPath = Storage::disk('public')->path($path);
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($pdfPath);
                    $pages = count($pdf->getPages());
                    $validated['pages'] = $pages;
                } catch (\Exception $e) {
                    $validated['pages'] = null;
                }
            } elseif (empty($validated['file_path'])) {
                return response()->json([
                    'message' => 'No file provided. Please upload a pdf_file or provide a file_path.'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $ebook = $user->ebooks()->create($validated);

            $ebook->load(['bookItem', 'ebookType']);

            return (new EBookResource($ebook))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create ebook',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */    public function show(Request $request, EBook $ebook) {
        // Always load required relationships
        $ebook->load(['bookmarks', 'notes', 'chatMessages', 'collections', 'ebookType']);
        $ebook->loadCount(['bookmarks', 'notes', 'chatMessages', 'collections']);

        // Include additional relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['bookItem', 'ebookType', 'bookmarks', 'notes', 'collections'];
            $additionalRelations = array_diff(array_intersect($relationships, $allowedRelations), ['bookmarks', 'notes', 'chatMessages', 'collections', 'ebookType']);
            
            if (!empty($additionalRelations)) {
                $ebook->load($additionalRelations);
            }

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
    public function update(UpdateEBookRequest $request, EBook $ebook) {
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
    public function destroy(EBook $ebook) {
        try {
            DB::beginTransaction();

            // Check if there are any references to this ebook
            if (
                $ebook->bookmarks()->count() > 0 ||
                $ebook->notes()->count() > 0 ||
                $ebook->collections()->count() > 0
            ) {
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
