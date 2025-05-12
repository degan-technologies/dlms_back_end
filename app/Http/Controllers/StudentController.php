<?php

namespace App\Http\Controllers;

use App\Models\Students;
use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // ... existing code ...
public function index(Request $request)
{
    $query = Students::query();

    // Global search
    if ($request->filled('global')) {
        $search = $request->global;
        $query->where(function ($q) use ($search) {
            $q->where('FirstName', 'like', "%$search%")
              ->orWhere('LastName', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%")
              ->orWhere('grade', 'like', "%$search%")
              ->orWhere('section', 'like', "%$search%")
              ->orWhere('gender', 'like', "%$search%")
              ->orWhere('phone_no', 'like', "%$search%");
        });
    }

    // Individual field filters
    if ($request->filled('FirstName')) {
        $query->where('FirstName', 'like', '%' . $request->FirstName . '%');
    }
    if ($request->filled('LastName')) {
        $query->where('LastName', 'like', '%' . $request->LastName . '%');
    }
    if ($request->filled('email')) {
        $query->where('email', 'like', '%' . $request->email . '%');
    }
    if ($request->filled('grade')) {
        $query->where('grade', 'like', '%' . $request->grade . '%');
    }
    if ($request->filled('section')) {
        $query->where('section', 'like', '%' . $request->section . '%');
    }
    if ($request->filled('gender')) {
        $query->where('gender', 'like', '%' . $request->gender . '%');
    }
    if ($request->filled('phone_no')) {
        $query->where('phone_no', 'like', '%' . $request->phone_no . '%');
    }

    return StudentResource::collection($query->paginate($request->per_page ?? 10));
}
// ... existing code ...

    public function store(Request $request)
    {
        $validated = $request->validate([
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
            'Address' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:50',
            'phone_no' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'section' => 'nullable|string|max:50',
            'gender' => 'required|string',
        ]);

        $user = $request->user();
        $branchId = $user->library_branch_id;
        // $branchId = 1;

        $student = Students::create([
            'FirstName' => $validated['FirstName'],
            'LastName' => $validated['LastName'],
            'Address' => $validated['Address'] ?? null,
            'grade' => $validated['grade'] ?? null,
            'section' => $validated['section'] ?? null,
            'phone_no' => $validated['phone_no']?? null,
            'email' => $validated['email']?? null,
            'gender' => $validated['gender'],
            'user_id' => $user->id,
            'BranchID' => $branchId,
        ]);

        return new StudentResource($student);
    }
// ... existing code ...
public function batchStore(Request $request)
{
    $studentsData = $request->input('students', []);
    $created = [];

    foreach ($studentsData as $data) {
        $validated = validator($data, [
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
            'Address' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:50',
            'phone_no' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'section' => 'nullable|string|max:50',
            'gender' => 'required|string',
        ])->validate();

       $user = $request->user();
       $branchId = $user->library_branch_id;
        $student = Students::create([
            'FirstName' => $validated['FirstName'],
            'LastName' => $validated['LastName'],
            'Address' => $validated['Address'] ?? null,
            'grade' => $validated['grade'] ?? null,
            'section' => $validated['section'] ?? null,
            'phone_no' => $validated['phone_no'] ?? null,
            'email' => $validated['email'] ?? null,
            'gender' => $validated['gender'],
            'user_id' => $user->id,
            'BranchID' => $branchId,
        ]);
        $created[] = $student;
    }

    return StudentResource::collection($created);
}
// ... existing code ...
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
            'phone_no' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
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
