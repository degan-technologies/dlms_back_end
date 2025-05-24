<?php

namespace App\Http\Controllers\API\V1;

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
            $query->where('category_name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        }

        // Apply specific filters
        if ($request->has('category_name')) {
            $query->where('category_name', 'like', "%{$request->category_name}%");
        }

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        if ($request->has('only_parents')) {
            $query->whereNull('parent_id');
        }

        // Sort by
        $sortField = $request->query('sort_by', 'category_name');
        $sortDir = $request->query('sort_dir', 'asc');

        // Allow sorting by id, category_name, or created_at
        if (in_array($sortField, ['id', 'category_name', 'created_at'])) {
            $query->orderBy($sortField, $sortDir);
        }

        // Eager load relationships if needed
        if ($request->has('with_children') && $request->with_children) {
            $query->with('children');
        }

        // Paginate
        $perPage = $request->query('per_page', 10);
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
        $validated = $request->only(['category_name']); // allow both fields

        // Prevent updating to a duplicate category name
        if (isset($validated['category_name'])) {
            $exists = Category::where('category_name', $validated['category_name'])
                ->where('id', '!=', $category->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Category name already exists.'
                ], Response::HTTP_CONFLICT);
            }
        }

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
        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with child categories. Please reassign or delete them first.'
            ], Response::HTTP_CONFLICT);
        }
        if ($category->bookItems()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with associated book items. Please reassign them first.'
            ], Response::HTTP_CONFLICT);
        }
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully'], Response::HTTP_OK);
    }
}
