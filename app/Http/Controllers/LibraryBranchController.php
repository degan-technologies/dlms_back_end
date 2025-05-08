<?php

namespace App\Http\Controllers;

use App\Models\LibraryBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LibraryBranchController extends Controller
{
    /**
     * Display a listing of library branches.
     */
    public function index()
    {
        $branches = LibraryBranch::with('library')->get();
        return response()->json($branches);
    }

    /**
     * Store a newly created library branch.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'opening_hours' => 'nullable|string',
            'library_id' => 'required|exists:libraries,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $branch = LibraryBranch::create($request->all());
        return response()->json($branch, 201);
    }

    /**
     * Display the specified library branch.
     */
    public function show($id)
    {
        $branch = LibraryBranch::with('library')->findOrFail($id);
        return response()->json($branch);
    }

    /**
     * Update the specified library branch.
     */
    public function update(Request $request, $id)
    {
        $branch = LibraryBranch::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'branch_name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'opening_hours' => 'nullable|string',
            'library_id' => 'sometimes|required|exists:libraries,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $branch->update($request->all());
        return response()->json($branch);
    }

    /**
     * Remove the specified library branch.
     */
    public function destroy($id)
    {
        $branch = LibraryBranch::findOrFail($id);
        $branch->delete();
        return response()->json(['message' => 'Library branch deleted successfully']);
    }
}