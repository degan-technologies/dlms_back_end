<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Http\Resources\AdminResource;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::query();
    
        // Global search
        if ($request->filled('global')) {
            $search = $request->global;
            $query->where(function ($q) use ($search) {
                $q->where('FirstName', 'like', "%$search%")
                  ->orWhere('LastName', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
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
        if ($request->filled('phone_no')) {
            $query->where('phone_no', 'like', '%' . $request->phone_no . '%');
        }
    
        return AdminResource::collection($query->paginate($request->per_page ?? 10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone_no' => 'nullable|string|max:15',
        ]);

        $user = $request->user();
        $branchId = $user->library_branch_id;

        $admin = Admin::create([
            'FirstName' => $validated['FirstName'],
            'LastName' => $validated['LastName'],
            'email' => $validated['email'] ?? null,
            'phone_no' => $validated['phone_no'] ?? null,
            'user_id' => $user->id,
            'library_branch_id' => $branchId,
        ]);

        return new AdminResource($admin);
    }

    public function batchStore(Request $request)
    {
        $adminsData = $request->input('admins', []);
        $created = [];

        foreach ($adminsData as $data) {
            $validated = validator($data, [
                'FirstName' => 'required|string|max:100',
                'LastName' => 'required|string|max:100',
                'email' => 'nullable|email|max:255',
                'phone_no' => 'nullable|string|max:15',
            ])->validate();

            $user = $request->user();
            $branchId = $user->library_branch_id;
            $admin = Admin::create([
                'FirstName' => $validated['FirstName'],
                'LastName' => $validated['LastName'],
                'email' => $validated['email'] ?? null,
                'phone_no' => $validated['phone_no'] ?? null,
                'user_id' => $user->id,
                'library_branch_id' => $branchId,
            ]);
            $created[] = $admin;
        }

        return AdminResource::collection($created);
    }

    public function show($id)
    {
        $admin = Admin::findOrFail($id);
        return new AdminResource($admin);
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $validated = $request->validate([
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone_no' => 'nullable|string|max:15',
        ]);

        $admin->update($validated);

        return new AdminResource($admin);
    }

    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();
        return response()->json(null, 204);
    }
}
