<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Note\StoreNoteRequest;
use App\Http\Requests\V1\Note\UpdateNoteRequest;
use App\Http\Resources\V1\Note\NoteCollection;
use App\Http\Resources\V1\Note\NoteResource;
use App\Models\Note;
use App\Models\EBook;
use App\Models\OtherAsset;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Note::where('user_id', Auth::id());
        
        // Filter by notable type
        if ($request->has('type')) {
            $type = $request->type;
            if ($type === 'ebook') {
                $query->where('notable_type', EBook::class);
            } elseif ($type === 'other_asset') {
                $query->where('notable_type', OtherAsset::class);
            }
        }
        
        // Filter by notable ID
        if ($request->has('notable_id')) {
            $query->where('notable_id', $request->notable_id);
        }
        
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        // Add eager loading for included relationships
        if (!empty($includes)) {
            $query->with($includes);
        }
        
        // Sort results
        $sortField = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');
        $allowedSorts = ['created_at', 'page_number'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }
        
        // Paginate results
        $perPage = $request->query('per_page', 15);
        $notes = $query->paginate($perPage);
        
        return new NoteCollection($notes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\Note\StoreNoteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreNoteRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        
        // Determine the correct notable_type based on the type
        if ($validated['type'] === 'ebook') {
            $validated['notable_type'] = EBook::class;
        } elseif ($validated['type'] === 'other_asset') {
            $validated['notable_type'] = OtherAsset::class;
        }
        
        $note = Note::create($validated);
        
        return (new NoteResource($note))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Note $note)
    {
        // Check if the note belongs to the authenticated user
        if ($note->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        // Include relationships
        $includes = $request->query('include', '');
        $includes = array_filter(explode(',', $includes));
        
        if (!empty($includes)) {
            $note->loadMissing($includes);
        }
        
        return new NoteResource($note);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\V1\Note\UpdateNoteRequest  $request
     * @param  \App\Models\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {
        // Check if the note belongs to the authenticated user
        if ($note->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        $validated = $request->validated();
        
        $note->update($validated);
        
        return new NoteResource($note);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function destroy(Note $note)
    {
        // Check if the note belongs to the authenticated user
        if ($note->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        $note->delete();
        
        return response()->json(['message' => 'Note deleted successfully'], Response::HTTP_OK);
    }
}
