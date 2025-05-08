<?php

namespace App\Http\Controllers;

use App\Models\Students;
use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        return StudentResource::collection(Students::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
            'Address' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:50',
            'section' => 'nullable|string|max:50',
            'gender' => 'required|string',
        ]);

        $user = $request->user();
        $branchId = $user->library_branch_id;

        $student = Students::create([
            'FirstName' => $validated['FirstName'],
            'LastName' => $validated['LastName'],
            'Address' => $validated['Address'] ?? null,
            'grade' => $validated['grade'] ?? null,
            'section' => $validated['section'] ?? null,
            'gender' => $validated['gender'],
            'user_id' => $user->id,
            'BranchID' => $branchId,
        ]);

        return new StudentResource($student);
    }

    public function show($id)
    {
        $student = Students::findOrFail($id);
        return new StudentResource($student);
    }

    public function update(Request $request, $id)
    {
        $student = Students::findOrFail($id);

        $validated = $request->validate([
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
            'Address' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:50',
            'section' => 'nullable|string|max:50',
            'gender' => 'required|string',
        ]);

        $student->update($validated);

        return new StudentResource($student);
    }

    public function destroy($id)
    {
        $student = Students::findOrFail($id);
        $student->delete();
        return response()->json(null, 204);
    }
}
