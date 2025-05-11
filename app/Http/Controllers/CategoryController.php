<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\Category\CategoryResource;
class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        // Read per-page from the query string, default to 10
        $perPage = (int) $request->query('pageSize', 10);
    
        // Get paginated result
        $paginator = Category::paginate($perPage);
    
        // Create the resource collection
        $resource = CategoryResource::collection($paginator);
    
        // Add pagination meta explicitly if needed
        return response()->json([
            'data' => $resource->collection,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ], 200);
    }
    

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:categories,category_name',
        ]);

        $category = Category::create($validated);

        return new CategoryResource($category); // Use resource
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return new CategoryResource($category); // Use resource
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
       

        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:categories,category_name,'.$category->id,
        ]);

        $category->update($validated);

        return new CategoryResource($category); // Use resource
    }

    // In your Laravel controller
    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids');
    
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'Invalid or empty IDs.'], 422);
        }
    
        Category::whereIn('id', $ids)->delete();
    
        return response()->json(['message' => 'Categories deleted successfully.']);
    }
    

    /**
     * Soft‑delete the specified category.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully'], 200);
    }
}
