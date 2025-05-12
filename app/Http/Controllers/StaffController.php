<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Http\Resources\StaffResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::query();
    
        // Global search
        if ($request->filled('global')) {
            $search = $request->global;
            $query->where(function ($q) use ($search) {
                $q->where('FirstName', 'like', "%$search%")
                  ->orWhere('LastName', 'like', "%$search%")
                  ->orWhere('phone_no', 'like', "%$search%")
                  ->orWhere('department', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }
    
        // Individual field filters
        if ($request->filled('FirstName')) {
            $query->where('FirstName', 'like', '%' . $request->FirstName . '%');
        }
        if ($request->filled('LastName')) {
            $query->where('LastName', 'like', '%' . $request->LastName . '%');
        }
        if ($request->filled('phone_no')) {
            $query->where('phone_no', 'like', '%' . $request->phone_no . '%');
        }
        if ($request->filled('department')) {
            $query->where('department', 'like', '%' . $request->department . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
    
        return StaffResource::collection($query->paginate($request->per_page ?? 10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
            'department' =>'required|string|max:100',
            'phone_no' =>'nullable|string|max:100',
            'email' =>'nullable|string|max:100',
        ]);
        $user = $request->user();
        $libraryBranchId = $user->library_branch_id;

        // If library_branch_id is a relationship, use: $user->libraryBranch->id
        $staff = Staff::create([
            'FirstName' => $validated['FirstName'],
            'LastName' => $validated['LastName'],
            'department' => $validated['department'],
            'phone_no' => $validated['phone_no'],
            'email' => $validated['email'],
            'user_id' =>$user->id,
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
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
            'department' =>'required|string|max:100',
            'phone_no' =>'nullable|string|max:100',
            'email' =>'nullable|string|max:100',
        ]);

        $staff->update($validated);

        return new StaffResource($staff);
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();
        return response()->json(null, 204);
    }

 // ... existing code ...
public function storeBulk(Request $request)
{
    $validated = $request->validate([
        'staff' => 'required|array|min:1',
        'staff.*.FirstName' => 'required|string|max:100',
        'staff.*.LastName' => 'required|string|max:100',
        'staff.*.department' => 'required|string|max:100',
        'staff.*.phone_no' => 'nullable|string|max:100',
        'staff.*.email' => 'nullable|string|max:100',
    ]);

    $user = $request->user();
    $libraryBranchId = $user->library_branch_id;

    $createdStaff = [];
    foreach ($validated['staff'] as $staffData) {
        $createdStaff[] = Staff::create([
            'FirstName' => $staffData['FirstName'],
            'LastName' => $staffData['LastName'],
            'department' => $staffData['department'],
            'phone_no' => $staffData['phone_no'] ?? null,
            'email' => $staffData['email'] ?? null,
            'user_id' => $user->id,
            'library_branch_id' => $libraryBranchId, 
        ]);
    }

    return StaffResource::collection(collect($createdStaff));
}
// ... existing code ...
}