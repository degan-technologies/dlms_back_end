<?php

namespace App\Http\Controllers\Note;

use App\Http\Controllers\Controller;
use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Http\Resources\Note\NoteCollection;
use App\Http\Resources\Note\NoteResource;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NoteController extends Controller
{
    /**
     * Display a listing of the notes.
     */    public function index(Request $request)
    {
        $query = Note::query();
        
        // Only show the current user's notes
        $query->where('user_id', auth()->id());
        
        // Apply filters if provided
        if ($request->has('e_book_id')) {
            $query->where('e_book_id', $request->e_book_id);
        }
        
        // Filter by multiple ebook IDs
        if ($request->has('e_book_ids')) {
            $ebookIds = explode(',', $request->e_book_ids);
            $query->whereIn('e_book_id', $ebookIds);
        }
        
        // Filter by page number
        if ($request->has('page_number')) {
            $query->where('page_number', $request->page_number);
        }
        
        // Filter by page number range
        if ($request->has('page_from') && $request->has('page_to')) {
            $query->whereBetween('page_number', [$request->page_from, $request->page_to]);
        }
        
        // Search in content
        if ($request->has('content')) {
            $query->where('content', 'like', '%' . $request->content . '%');
        }
        
        // Search in highlight text
        if ($request->has('highlight_text')) {
            $query->where('highlight_text', 'like', '%' . $request->highlight_text . '%');
        }
        
        // Search in all text fields
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('content', 'like', '%' . $searchTerm . '%')
                  ->orWhere('highlight_text', 'like', '%' . $searchTerm . '%')
                  ->orWhere('metadata', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['ebook', 'user'];
            $query->with(array_intersect($relationships, $allowedRelations));
            
            // Special case for nested relationships
            if (in_array('ebook.bookItem', $relationships)) {
                $query->with('ebook.bookItem');
            }
        }
        
        // Custom sorting
        if ($request->has('sort_by') && in_array($request->sort_by, ['created_at', 'updated_at', 'page_number'])) {
            $direction = $request->has('sort_direction') && $request->sort_direction === 'asc' ? 'asc' : 'desc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->latest(); // Default sort by created_at desc
        }
        
        // Paginate the results
        $perPage = $request->per_page ?? 15;
        $notes = $query->paginate($perPage);
        
        return new NoteCollection($notes);
    }

    /**
     * Store a newly created note in storage.
     */
    public function store(StoreNoteRequest $request)
    {
        $validated = $request->validated();
        
        // Add the authenticated user's ID
        $validated['user_id'] = auth()->id();
        
        // Create the note
        $note = Note::create($validated);
        
        // Load relationships for the response
        $note->load('ebook.bookItem');
        
        return new NoteResource($note);
    }

    /**
     * Display the specified note.
     */
    public function show(Request $request, Note $note)
    {
        // Check if the note belongs to the authenticated user
        if ($note->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['ebook', 'user'];
            $note->load(array_intersect($relationships, $allowedRelations));
            
            // Special case for nested relationships
            if (in_array('ebook.bookItem', $relationships)) {
                $note->load('ebook.bookItem');
            }
        }
        
        return new NoteResource($note);
    }

    /**
     * Update the specified note in storage.
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {
        $validated = $request->validated();
        
        $note->update($validated);
        
        // Load relationships for the response
        $note->load('ebook.bookItem');
        
        return new NoteResource($note);
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(Note $note)
    {
        // Check if the note belongs to the authenticated user
        if ($note->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        $note->delete();
        
        return response()->json(['message' => 'Note deleted successfully']);
    }
}
