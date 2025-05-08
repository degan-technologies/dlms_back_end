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
    public function index()
    {
        return response()->json(Library::all());
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

        $request->validate([
            'name' => 'required|string|unique:libraries',
            'address' => 'required|string',
            'contact_number' => 'required|string',
        ]);

        $library = Library::create($request->all());

        return response()->json([
            'message' => 'Library created successfully.',
            'library' => new LibraryResource($library)
        ], 201);
    }

    // Update a library (restricted to super-admin)
    public function update(Request $request, $id)
    {
        $this->authorizeSuperAdminOrAdmin();

        $library = Library::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|unique:libraries,name,' . $library->id,
            'address' => 'sometimes|string',
            'contact_number' => 'sometimes|string',
        ]);

        $library->update($request->all());

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

    // Utility method for checking role
    protected function authorizeSuperAdminOrAdmin()
    {
        if (!(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin'))) {
            abort(403, 'Unauthorized action. Admin or Super-admin access only.');
        }
    }
}
