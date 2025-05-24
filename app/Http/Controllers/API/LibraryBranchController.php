<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\LibraryBranchResource;
use App\Models\LibraryBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibraryBranchController extends Controller
{
    // List all branches (accessible to all authenticated users)
    public function index()
    {
        return response()->json(LibraryBranch::all());
    }

    // Show a specific branch
    public function show($id)
    {
        $branch = LibraryBranch::findOrFail($id);
        return response()->json($branch);
    }

    // Create a new branch (only for super-admin)
    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'branch_name' => 'required|string|unique:library_branches',
            'address' => 'nullable|string',
            'contact_number' => [
                'nullable',
                'string',
                'regex:/^\+251[0-9]{9}$/'
            ],
            'email' => 'nullable|email',
            'opening_hours' => [
                'nullable',
                'string',
                'regex:/^([01]\d|2[0-3]):([01]\d)-([01]\d|2[0-3]):([01]\d)$/', // Validate HH:mm-HH:mm format
            ],
            'location' => [
                'required',
                'string',
                'regex:/^(https?:\/\/)?(www\.)?(google\.com\/maps|goo\.gl\/maps)\/[^\s]+$/', // Accept Google Maps URLs
            ],
        ]);

        $branch = LibraryBranch::create($validated);

        return response()->json([
            'message' => 'Library branch created successfully.',
            'branch' => new LibraryBranchResource($branch)
        ], 201);
    }

    // Update branch info (only for super-admin)
    public function update(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $branch = LibraryBranch::findOrFail($id);

        $validated = $request->validate([
            'branch_name' => 'sometimes|string|unique:library_branches,branch_name,' . $branch->id,
            'address' => 'nullable|string',
            'contact_number' => [
                'nullable',
                'string',
                'regex:/^\+251[0-9]{9}$/'
            ],
            'email' => 'nullable|email',
            'opening_hours' => [
                'nullable',
                'string',
                'regex:/^([01]\d|2[0-3]):([0-5]\d)-([01]\d|2[0-3]):([0-5]\d)$/', // Validate HH:mm-HH:mm format
            ],
            'location' => [
                'required',
                'string',
                'regex:/^(https?:\/\/)?(www\.)?(google\.com\/maps|goo\.gl\/maps)\/[^\s]+$/', // Accept Google Maps URLs
            ],
        ]);

        $branch->update($validated);

        return response()->json([
            'message' => 'Library branch updated successfully.',
            'branch' => new LibraryBranchResource($branch)
        ]);
    }

    // Delete a branch (only for super-admin)
    public function destroy($id)
    {
        $this->authorizeSuperAdmin();

        $branch = LibraryBranch::findOrFail($id);
        $branch->delete();

        return response()->json([
            'message' => 'Library branch deleted successfully.',
            'branch' => new LibraryBranchResource($branch)
    ]);
    }
    public function bulkDelete(Request $request)
    {
        $this->authorizeSuperAdmin();

        $ids = $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['message' => 'Invalid or no IDs provided.'], 400);
        }

        $validIds = LibraryBranch::whereIn('id', $ids)->pluck('id')->toArray();

        if (empty($validIds)) {
            return response()->json(['message' => 'No valid IDs found.'], 400);
        }

        LibraryBranch::destroy($validIds);

        return response()->json(['message' => 'Library branches deleted successfully.']);
    }

    // Utility: Check super-admin role
    protected function authorizeSuperAdmin()
    {
        if (!Auth::user()?->hasRole('superadmin')) {
            abort(403, 'Unauthorized. Only super-admins can perform this action.');
        }
    }

}
