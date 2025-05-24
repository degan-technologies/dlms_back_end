<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssetTypeCollection;
use App\Http\Resources\AssetTypeResource;
use App\Models\AssetType;
use App\Http\Requests\StoreAssetTypeRequest;
use App\Http\Requests\UpdateAssetTypeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = AssetType::query();

            // Apply filters
            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('is_electronic')) {
                $query->where('is_electronic', $request->boolean('is_electronic'));
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('file_type_category')) {
                $query->where('file_type_category', $request->file_type_category);
            }

            // Sort
            $sortField = $request->input('sort_by', 'name');
            $sortDirection = $request->input('sort_direction', 'asc');
            $allowedSortFields = ['name', 'created_at', 'is_electronic', 'file_type_category'];
            
            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Include counts
            if ($request->boolean('with_counts', false)) {
                $query->withCount('otherAssets');
            }

            $perPage = $request->input('per_page', 15);
            $assetTypes = $query->paginate($perPage);

            return new AssetTypeCollection($assetTypes);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve asset types: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve asset types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAssetTypeRequest $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            
            // Handle JSON fields
            if (isset($validatedData['allowed_extensions']) && is_array($validatedData['allowed_extensions'])) {
                $validatedData['allowed_extensions'] = json_encode($validatedData['allowed_extensions']);
            }
            
            if (isset($validatedData['metadata']) && is_array($validatedData['metadata'])) {
                $validatedData['metadata'] = json_encode($validatedData['metadata']);
            }
            
            $assetType = AssetType::create($validatedData);

            DB::commit();
            
            return new AssetTypeResource($assetType);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create asset type: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to create asset type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $assetType = AssetType::findOrFail($id);
            return new AssetTypeResource($assetType);
            
        } catch (\Exception $e) {
            Log::error('Failed to retrieve asset type: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Asset type not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAssetTypeRequest $request, int $id)
    {
        try {
            DB::beginTransaction();

            $assetType = AssetType::findOrFail($id);
            $validatedData = $request->validated();
            
            // Handle JSON fields
            if (isset($validatedData['allowed_extensions']) && is_array($validatedData['allowed_extensions'])) {
                $validatedData['allowed_extensions'] = json_encode($validatedData['allowed_extensions']);
            }
            
            if (isset($validatedData['metadata']) && is_array($validatedData['metadata'])) {
                $validatedData['metadata'] = json_encode($validatedData['metadata']);
            }

            $assetType->update($validatedData);
            
            DB::commit();
            
            return new AssetTypeResource($assetType);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update asset type: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to update asset type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            DB::beginTransaction();

            $assetType = AssetType::findOrFail($id);
            
            // Check if asset type is in use
            if ($assetType->otherAssets()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete asset type as it is in use by other assets',
                ], 422);
            }
            
            $assetType->delete();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Asset type deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete asset type: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to delete asset type',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}