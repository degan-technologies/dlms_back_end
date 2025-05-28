<?php

namespace App\Http\Controllers;

use App\Models\Library;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    // List all libraries
    public function index()
    {
        return response()->json(Library::all(), 200);
    }

    // Create a new library
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
        ]);

        $library = Library::create($request->all());

        return response()->json($library, 201);
    }

    // Show a single library
    public function show($id)
    {
        $library = Library::find($id);

        if (!$library) {
            return response()->json(['message' => 'Library not found'], 404);
        }

        return response()->json($library, 200);
    }

    // Update a library
    public function update(Request $request, $id)
    {
        $library = Library::find($id);

        if (!$library) {
            return response()->json(['message' => 'Library not found'], 404);
        }

        $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'address'        => 'sometimes|required|string|max:255',
            'contact_number' => 'sometimes|required|string|max:20',
        ]);

        $library->update($request->all());

        return response()->json($library, 200);
    }

    // Soft delete a library
    public function destroy($id)
    {
        $library = Library::find($id);

        if (!$library) {
            return response()->json(['message' => 'Library not found'], 404);
        }

        $library->delete();

        return response()->json(['message' => 'Library deleted'], 200);
    }
}
