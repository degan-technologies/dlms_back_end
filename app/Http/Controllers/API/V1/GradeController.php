<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Grade\StoreGradeRequest;
use App\Http\Requests\V1\Grade\UpdateGradeRequest;
use App\Http\Resources\V1\Grade\GradeCollection;
use App\Http\Resources\V1\Grade\GradeResource;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Grade::query();
        
        // Apply search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('level', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply specific filters
        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }
        
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }
        
        // Sort by
        $sortField = $request->query('sort_by', 'level');
        $sortDir = $request->query('sort_dir', 'asc');
        
        if (in_array($sortField, ['name', 'level', 'created_at'])) {
            $query->orderBy($sortField, $sortDir);
        }
        
        // Paginate
        $perPage = $request->query('per_page', 15);
        $grades = $query->paginate($perPage);
        
        return new GradeCollection($grades);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\Grade\StoreGradeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGradeRequest $request)
    {
        $validated = $request->validated();
        
        $grade = Grade::create($validated);
        
        return new GradeResource($grade);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function show(Grade $grade)
    {
        return new GradeResource($grade);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\Grade\UpdateGradeRequest  $request
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGradeRequest $request, Grade $grade)
    {
        $validated = $request->validated();
        
        $grade->update($validated);
        
        return new GradeResource($grade);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function destroy(Grade $grade)
    {
        // Check if there are any book items associated with this grade
        if ($grade->bookItems()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete grade because it has associated book items.'
            ], Response::HTTP_CONFLICT);
        }
        
        $grade->delete();
        
        return response()->json([
            'message' => 'Grade deleted successfully'
        ], Response::HTTP_OK);
    }
}
