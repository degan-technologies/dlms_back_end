<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\LibraryResource;
use App\Models\Library;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller
{
    // List all libraries (available to any authenticated user)
    public function index(Request $request)
    {
        $authUser = auth('api')->user();

        $query = Library::query();

        // Apply global search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('contact_number', 'like', '%' . $search . '%');
            });
        }

        // Apply branch filtering if passed from query
        if ($request->has('library_branch_id')) {
            $query->where('library_branch_id', $request->input('library_branch_id'));
        } elseif ($authUser && !$authUser->hasRole('superadmin')) {
            // If the user is not superadmin, limit to their own branch
            $query->where('library_branch_id', $authUser->library_branch_id);
        }

        $libraries = $query->get();

        return response()->json(LibraryResource::collection($libraries));
    }




    // Show single library (available to any authenticated user)
    public function show($id)
    {
        $library = Library::findOrFail($id);
        return response()->json($library);
    }

    // Store a new library (restricted to super-admin)
    public function store(Request $request)
    {
        $this->authorizeSuperAdminOrAdmin();

        $authUser = auth()->user();

        // Validate only name and contact_number because library_branch_id comes from auth user
        $request->validate([
            'name' => 'required|string|unique:libraries',
            'contact_number' => 'required|string',
        ]);

        $library = Library::create([
            'name' => $request->name,
            'contact_number' => $request->contact_number,
            // Use the authenticated user's library_branch_id
            'library_branch_id' => $authUser->library_branch_id,
        ]);

        return response()->json([
            'message' => 'Library created successfully.',
            'library' => new LibraryResource($library)
        ], 201);
    }



    // Update a library (restricted to super-admin)
    public function update(Request $request, $id)
    {
        $this->authorizeSuperAdminOrAdmin();

        $authUser = auth()->user();

        // Find the library and ensure it belongs to the same branch as the user
        $library = Library::where('id', $id)
            ->where('library_branch_id', $authUser->library_branch_id)
            ->firstOrFail();

        // Validate only updatable fields
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:libraries,name,' . $library->id,
            'contact_number' => 'sometimes|string',
        ]);

        $library->update($validated);

        return response()->json([
            'message' => 'Library updated successfully.',
            'library' => new LibraryResource($library)
        ]);
    }


    // Delete a library (restricted to super-admin)
    public function destroy($id)
    {
        $this->authorizeSuperAdminOrAdmin();

        $library = Library::findOrFail($id);
        $library->delete();

        return response()->json(['message' => 'Library deleted successfully.']);
    }

    // bulk delete libraries (restricted to super-admin)
    public function bulkDelete(Request $request)
    {
        $this->authorizeSuperAdminOrAdmin();

        $ids = $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['message' => 'Invalid or no IDs provided.'], 400);
        }

        $validIds = Library::whereIn('id', $ids)->pluck('id')->toArray();

        if (empty($validIds)) {
            return response()->json(['message' => 'No valid IDs found.'], 400);
        }

        Library::destroy($validIds);

        return response()->json(['message' => 'Libraries deleted successfully.']);
    }

    // Utility method for checking role
    protected function authorizeSuperAdminOrAdmin()
    {
        if (!(Auth::user()->hasRole('admin'))) {
            abort(403, 'Unauthorized action. Admin only do this.');
        }
    }
}
