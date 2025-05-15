<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Language\StoreLanguageRequest;
use App\Http\Requests\V1\Language\UpdateLanguageRequest;
use App\Http\Resources\V1\Language\LanguageCollection;
use App\Http\Resources\V1\Language\LanguageResource;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Language::query();
        
        // Apply search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        // Apply specific filters
        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }
        
        if ($request->has('code')) {
            $query->where('code', 'like', "%{$request->code}%");
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        // Sort by
        $sortField = $request->query('sort_by', 'name');
        $sortDir = $request->query('sort_dir', 'asc');
        
        if (in_array($sortField, ['name', 'code', 'created_at'])) {
            $query->orderBy($sortField, $sortDir);
        }
        
        // Paginate
        $perPage = $request->query('per_page', 15);
        $languages = $query->paginate($perPage);
        
        return new LanguageCollection($languages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\Language\StoreLanguageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLanguageRequest $request)
    {
        $validated = $request->validated();
        
        $language = Language::create($validated);
        
        return new LanguageResource($language);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Language  $language
     * @return \Illuminate\Http\Response
     */
    public function show(Language $language)
    {
        return new LanguageResource($language);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\Language\UpdateLanguageRequest  $request
     * @param  \App\Models\Language  $language
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLanguageRequest $request, Language $language)
    {
        $validated = $request->validated();
        
        $language->update($validated);
        
        return new LanguageResource($language);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Language  $language
     * @return \Illuminate\Http\Response
     */
    public function destroy(Language $language)
    {
        // Check if there are any book items associated with this language
        if ($language->bookItems()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete language because it has associated book items.'
            ], Response::HTTP_CONFLICT);
        }
        
        $language->delete();
        
        return response()->json([
            'message' => 'Language deleted successfully'
        ], Response::HTTP_OK);
    }
}
