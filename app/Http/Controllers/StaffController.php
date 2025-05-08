<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Http\Resources\StaffResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        return StaffResource::collection(Staff::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
        ]);
        $user = $request->user(); // Authenticated user
        // If library_branch_id is a direct attribute on User
        $libraryBranchId = $user->library_branch_id;
        // If library_branch_id is a relationship, use: $user->libraryBranch->id
        $staff = Staff::create([
            'FirstName' => $validated['FirstName'],
            'LastName' => $validated['LastName'],
            'user_id' => $user->id,
            'library_branch_id' => $libraryBranchId,
        ]);

        return new StaffResource($staff);
    }

    public function show(Staff $staff)
    {
        return new StaffResource($staff);
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'FirstName' => 'sometimes|required|string|max:100',
            'LastName' => 'sometimes|required|string|max:100',
        ]);

        $staff->update($validated);

        return new StaffResource($staff);
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();
        return response()->json(null, 204);
    }
}