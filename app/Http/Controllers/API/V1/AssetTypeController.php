<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AssetType\StoreAssetTypeRequest;
use App\Http\Requests\V1\AssetType\UpdateAssetTypeRequest;
use App\Http\Resources\V1\AssetType\AssetTypeCollection;
use App\Http\Resources\V1\AssetType\AssetTypeResource;
use App\Models\AssetType;
use Illuminate\Http\Request;

class AssetTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = AssetType::query();
        
        // Apply filters
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        if ($request->has('is_electronic')) {
            $query->where('is_electronic', $request->boolean('is_electronic'));
        }
        
        if ($request->has('requires_special_handling')) {
            $query->where('requires_special_handling', $request->boolean('requires_special_handling'));
        }
        
        // Sort results
        $sortField = $request->query('sort_by', 'name');
        $sortDirection = $request->query('sort_direction', 'asc');
        $allowedSorts = ['name', 'created_at', 'is_active'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 15);
        $assetTypes = $query->paginate($perPage);
        
        return new AssetTypeCollection($assetTypes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\AssetType\StoreAssetTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAssetTypeRequest $request)
    {
        $validated = $request->validated();
        
        $assetType = AssetType::create($validated);
        
        return new AssetTypeResource($assetType);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AssetType  $assetType
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, AssetType $assetType)
    {
        // Include relationships if needed in the future
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        if (!empty($includes)) {
            $assetType->loadMissing($includes);
        }
        
        return new AssetTypeResource($assetType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\AssetType\UpdateAssetTypeRequest  $request
     * @param  \App\Models\AssetType  $assetType
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAssetTypeRequest $request, AssetType $assetType)
    {
        $validated = $request->validated();
        
        $assetType->update($validated);
        
        return new AssetTypeResource($assetType);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AssetType  $assetType
     * @return \Illuminate\Http\Response
     */
    public function destroy(AssetType $assetType)
    {
        // Check if the asset type is in use by any OtherAsset
        $hasOtherAssets = $assetType->otherAssets()->exists();
        
        if ($hasOtherAssets) {
            return response()->json([
                'message' => 'Cannot delete asset type. It is currently used by one or more assets.'
            ], 409);
        }
        
        $assetType->delete();
        
        return response()->json(['message' => 'Asset type deleted successfully'], 200);
    }
}