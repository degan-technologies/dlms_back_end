<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\OtherAsset\StoreOtherAssetRequest;
use App\Http\Requests\V1\OtherAsset\UpdateOtherAssetRequest;
use App\Http\Resources\V1\OtherAsset\OtherAssetCollection;
use App\Http\Resources\V1\OtherAsset\OtherAssetResource;
use App\Models\OtherAsset;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OtherAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = OtherAsset::query()
            ->join('book_items', 'other_assets.book_item_id', '=', 'book_items.id');
        
        // Apply filters
        if ($request->has('title')) {
            $query->where('book_items.title', 'like', '%' . $request->title . '%');
        }
        
        if ($request->has('author')) {
            $query->where('book_items.author', 'like', '%' . $request->author . '%');
        }
        
        if ($request->has('isbn')) {
            $query->where('book_items.isbn', 'like', '%' . $request->isbn . '%');
        }
        
        if ($request->has('asset_type_id')) {
            $query->where('other_assets.asset_type_id', $request->asset_type_id);
        }
        
        if ($request->has('media_type')) {
            $query->where('other_assets.media_type', 'like', '%' . $request->media_type . '%');
        }
        
        if ($request->has('unique_id')) {
            $query->where('other_assets.unique_id', 'like', '%' . $request->unique_id . '%');
        }
        
        if ($request->has('physical_condition')) {
            $query->where('other_assets.physical_condition', $request->physical_condition);
        }
        
        if ($request->has('restricted_access')) {
            $query->where('other_assets.restricted_access', $request->boolean('restricted_access'));
        }
        
        if ($request->has('availability_status')) {
            $query->where('book_items.availability_status', $request->availability_status);
        }
        
        if ($request->has('library_branch_id')) {
            $query->where('book_items.library_branch_id', $request->library_branch_id);
        }
        
        if ($request->has('category_id')) {
            $query->where('book_items.category_id', $request->category_id);
        }
        
        if ($request->has('publisher_id')) {
            $query->where('book_items.publisher_id', $request->publisher_id);
        }
        
        // Select other assets
        $query->select('other_assets.*');
        
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        // Add eager loading for included relationships
        if (!empty($includes)) {
            $query->with($includes);
            
            // Always include bookItem and assetType
            if (!in_array('bookItem', $includes)) {
                $query->with('bookItem');
            }
            
            if (!in_array('assetType', $includes)) {
                $query->with('assetType');
            }
        } else {
            // Default to always include bookItem and assetType
            $query->with(['bookItem', 'assetType']);
        }
        
        // Sort results
        $sortField = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');
        $allowedSorts = ['media_type', 'unique_id', 'physical_condition', 'created_at'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy('other_assets.' . $sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        } else if (in_array($sortField, ['title', 'author', 'isbn', 'publication_year'])) {
            $query->orderBy('book_items.' . $sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 15);
        $otherAssets = $query->paginate($perPage);
        
        return new OtherAssetCollection($otherAssets);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\OtherAsset\StoreOtherAssetRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOtherAssetRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            // Extract other asset-specific fields
            $otherAssetData = [
                'asset_type_id' => $validated['asset_type_id'],
                'media_type' => $validated['media_type'] ?? null,
                'unique_id' => $validated['unique_id'] ?? null,
                'duration_minutes' => $validated['duration_minutes'] ?? null,
                'manufacturer' => $validated['manufacturer'] ?? null,
                'physical_condition' => $validated['physical_condition'] ?? null,
                'location_details' => $validated['location_details'] ?? null,
                'acquisition_date' => $validated['acquisition_date'] ?? null,
                'usage_instructions' => $validated['usage_instructions'] ?? null,
                'restricted_access' => $validated['restricted_access'] ?? false,
            ];
            
            // Extract book item fields
            $bookItemData = array_diff_key($validated, $otherAssetData);
            $bookItemData['item_type'] = 'other';
            
            // Create book item
            $bookItem = BookItem::create($bookItemData);
            
            // Create other asset with reference to book item
            $otherAssetData['book_item_id'] = $bookItem->id;
            $otherAsset = OtherAsset::create($otherAssetData);
            
            // Load the book item relationship for the response
            $otherAsset->load(['bookItem', 'assetType']);
            
            DB::commit();
            
            return new OtherAssetResource($otherAsset);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create other asset', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OtherAsset  $otherAsset
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, OtherAsset $otherAsset)
    {
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        if (!empty($includes)) {
            // Always include bookItem and assetType
            if (!in_array('bookItem', $includes)) {
                $includes[] = 'bookItem';
            }
            
            if (!in_array('assetType', $includes)) {
                $includes[] = 'assetType';
            }
            
            $otherAsset->loadMissing($includes);
        } else {
            // Default to always include bookItem and assetType
            $otherAsset->load(['bookItem', 'assetType']);
        }
        
        return new OtherAssetResource($otherAsset);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\OtherAsset\UpdateOtherAssetRequest  $request
     * @param  \App\Models\OtherAsset  $otherAsset
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOtherAssetRequest $request, OtherAsset $otherAsset)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            // Extract other asset-specific fields
            $otherAssetData = array_intersect_key($validated, [
                'asset_type_id' => '',
                'media_type' => '',
                'unique_id' => '',
                'duration_minutes' => '',
                'manufacturer' => '',
                'physical_condition' => '',
                'location_details' => '',
                'acquisition_date' => '',
                'usage_instructions' => '',
                'restricted_access' => '',
            ]);
            
            // Extract book item fields - remove other asset-specific fields
            $bookItemData = array_diff_key($validated, [
                'asset_type_id' => '',
                'media_type' => '',
                'unique_id' => '',
                'duration_minutes' => '',
                'manufacturer' => '',
                'physical_condition' => '',
                'location_details' => '',
                'acquisition_date' => '',
                'usage_instructions' => '',
                'restricted_access' => '',
            ]);
            
            // Update other asset
            if (!empty($otherAssetData)) {
                $otherAsset->update($otherAssetData);
            }
            
            // Update book item if we have book item data
            if (!empty($bookItemData) && $otherAsset->bookItem) {
                $otherAsset->bookItem->update($bookItemData);
            }
            
            // Reload other asset with related models
            $otherAsset->load(['bookItem', 'assetType']);
            
            DB::commit();
            
            return new OtherAssetResource($otherAsset);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update other asset', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OtherAsset  $otherAsset
     * @return \Illuminate\Http\Response
     */
    public function destroy(OtherAsset $otherAsset)
    {
        try {
            DB::beginTransaction();
            
            // Store book item ID before deleting the other asset
            $bookItemId = $otherAsset->book_item_id;
            
            // Delete the other asset
            $otherAsset->delete();
            
            // Delete the associated book item
            if ($bookItemId) {
                $bookItem = BookItem::find($bookItemId);
                if ($bookItem) {
                    $bookItem->delete();
                }
            }
            
            DB::commit();
            
            return response()->json(['message' => 'Asset deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete asset', 'error' => $e->getMessage()], 500);
        }
    }
}