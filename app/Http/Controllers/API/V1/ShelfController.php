<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Shelf\StoreShelfRequest;
use App\Http\Requests\V1\Shelf\UpdateShelfRequest;
use App\Http\Resources\V1\Shelf\ShelfCollection;
use App\Http\Resources\V1\Shelf\ShelfResource;
use App\Models\Shelf;
use Illuminate\Http\Request;

class ShelfController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Shelf::query();
        
        // Apply filters
        if ($request->has('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }
        
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        
        if ($request->has('library_branch_id')) {
            $query->where('library_branch_id', $request->library_branch_id);
        }
        
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        // Add eager loading for included relationships
        if (!empty($includes)) {
            $query->with($includes);
        }
        
        // Sort results
        $sortField = $request->query('sort_by', 'code');
        $sortDirection = $request->query('sort_direction', 'asc');
        $allowedSorts = ['code', 'location', 'capacity', 'created_at'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 15);
        $shelves = $query->paginate($perPage);
        
        return new ShelfCollection($shelves);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\Shelf\StoreShelfRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreShelfRequest $request)
    {
        $validated = $request->validated();
        
        $shelf = Shelf::create($validated);
        
        return new ShelfResource($shelf);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shelf  $shelf
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Shelf $shelf)
    {
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        if (!empty($includes)) {
            $shelf->loadMissing($includes);
        }
        
        return new ShelfResource($shelf);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\Shelf\UpdateShelfRequest  $request
     * @param  \App\Models\Shelf  $shelf
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateShelfRequest $request, Shelf $shelf)
    {
        $validated = $request->validated();
        
        $shelf->update($validated);
        
        return new ShelfResource($shelf);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shelf  $shelf
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shelf $shelf)
    {
        // Check if the shelf is in use by any BookItem
        $hasBookItems = $shelf->bookItems()->exists();
        
        if ($hasBookItems) {
            return response()->json([
                'message' => 'Cannot delete shelf. It currently contains one or more book items.'
            ], 409);
        }
        
        $shelf->delete();
        
        return response()->json(['message' => 'Shelf deleted successfully'], 200);
    }
}