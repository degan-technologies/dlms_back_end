<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Category\StoreCategoryRequest;
use App\Http\Requests\V1\Category\UpdateCategoryRequest;
use App\Http\Resources\V1\Category\CategoryCollection;
use App\Http\Resources\V1\Category\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Category::query();
        
        // Apply search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
        }
        
        // Apply specific filters
        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }
        
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }
        
        if ($request->has('only_parents')) {
            $query->whereNull('parent_id');
        }
        
        // Sort by
        $sortField = $request->query('sort_by', 'name');
        $sortDir = $request->query('sort_dir', 'asc');
        
        if (in_array($sortField, ['name', 'created_at'])) {
            $query->orderBy($sortField, $sortDir);
        }
        
        // Eager load relationships if needed
        if ($request->has('with_children') && $request->with_children) {
            $query->with('children');
        }
        
        // Paginate
        $perPage = $request->query('per_page', 15);
        $categories = $query->paginate($perPage);
        
        return new CategoryCollection($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\Category\StoreCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        $category = Category::create($validated);
        
        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Category $category)
    {
        if ($request->has('with_children') && $request->with_children) {
            $category->load('children');
        }
        
        if ($request->has('with_book_items') && $request->with_book_items) {
            $category->load('bookItems');
        }
        
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\Category\UpdateCategoryRequest  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();
        $category->update($validated);
        
        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        // Check if has children or book items before deleting
        if ($category->children()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with child categories. Please reassign or delete them first.'
            ], Response::HTTP_CONFLICT);
        }
        
        if ($category->bookItems()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated book items. Please reassign them first.'
            ], Response::HTTP_CONFLICT);
        }
        
        $category->delete();
        
        return response()->json(['message' => 'Category deleted successfully'], Response::HTTP_OK);
    }
}
