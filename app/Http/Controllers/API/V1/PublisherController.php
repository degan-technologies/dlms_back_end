<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Publisher\StorePublisherRequest;
use App\Http\Requests\V1\Publisher\UpdatePublisherRequest;
use App\Http\Resources\V1\Publisher\PublisherCollection;
use App\Http\Resources\V1\Publisher\PublisherResource;
use App\Models\Publisher;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Publisher::query();
        
        // Apply filters
        if ($request->has('PublisherName')) {
            $query->where('PublisherName', 'like', '%' . $request->PublisherName . '%');
        }
        
        if ($request->has('Address')) {
            $query->where('Address', 'like', '%' . $request->Address . '%');
        }
        
        if ($request->has('ContactInfo')) {
            $query->where('ContactInfo', 'like', '%' . $request->ContactInfo . '%');
        }
        
        // Sort results
        $sortField = $request->query('sort_by', 'PublisherName');
        $sortDirection = $request->query('sort_direction', 'asc');
        $allowedSorts = ['PublisherName', 'PublisherID', 'created_at'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 15);
        $publishers = $query->paginate($perPage);
        
        return new PublisherCollection($publishers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\Publisher\StorePublisherRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePublisherRequest $request)
    {
        $validated = $request->validated();
        
        $publisher = Publisher::create($validated);
        
        return new PublisherResource($publisher);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Publisher $publisher)
    {
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        if (!empty($includes)) {
            $publisher->loadMissing($includes);
        }
        
        return new PublisherResource($publisher);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\Publisher\UpdatePublisherRequest  $request
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePublisherRequest $request, Publisher $publisher)
    {
        $validated = $request->validated();
        
        $publisher->update($validated);
        
        return new PublisherResource($publisher);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\Http\Response
     */
    public function destroy(Publisher $publisher)
    {
        // Check if the publisher is in use by any BookItem
        $hasBookItems = $publisher->bookItems()->exists();
        
        if ($hasBookItems) {
            return response()->json([
                'message' => 'Cannot delete publisher. It is currently associated with one or more book items.'
            ], 409);
        }
        
        $publisher->delete();
        
        return response()->json(['message' => 'Publisher deleted successfully'], 200);
    }
}