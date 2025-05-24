<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Http\Resources\StaffResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Container\Attributes\Auth;
use Laravel\Pail\ValueObjects\Origin\Console;

class StaffController extends Controller
{
    public function index(Request $request)
{
    $query = Staff::with('user');
    
    $authUser = auth('api')->user();
    
    if ($authUser && $authUser->hasRole('superadmin')) {
        $query->whereRaw('LOWER(department) LIKE ?', ['%admin%']);
    } elseif ($authUser && $authUser->hasRole('admin')) {
        $query->whereRaw('LOWER(department) NOT LIKE ?', ['%admin%']);
    }
    
    if ($request->filled('global')) {
        $search = strtolower($request->global);
        $query->where(function ($q) use ($search) {
            $q->whereRaw('LOWER(first_name) LIKE ?', ["%$search%"])
              ->orWhereRaw('LOWER(last_name) LIKE ?', ["%$search%"])
              ->orWhereRaw('LOWER(department) LIKE ?', ["%$search%"]);
        });
    }
    
    return StaffResource::collection(
        $query->paginate($request->per_page ?? 10)
    );
}

    
    
    
    
    

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'department' => 'required|string|max:100',
                'phone_no' => 'nullable|string|max:100',
                'email' => 'nullable|string|max:100',
                'username' => 'required|string|unique:users',
                'password' => 'required|string|min:4',
            ]);

            $authUser = $request->user();

            $user = User::create([
                'username' => $validated['username'],
                'password' => bcrypt($validated['password']),
                'email' => $validated['email'] ?? null,
                'phone_no' => $validated['phone_no'] ?? null,
                'library_branch_id' => $authUser->library_branch_id,
            ]);

            $staff = Staff::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'department' => $validated['department'],
                'user_id' => $user->id,
                'library_branch_id' => $authUser->library_branch_id,
            ]);

            DB::commit();
            return new StaffResource($staff);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Staff $staff)
    {
        return new StaffResource($staff);
    }

    public function update(Request $request, Staff $staff)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'department' => 'required|string|max:100',
                'phone_no' => 'nullable|string|max:100',
                'email' => 'nullable|string|max:100',
                'username' => 'string|unique:users,username,' . $staff->user_id,
                'password' => 'string|min:4',
            ]);

            $staff->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'department' => $validated['department'],         
            ]);

            if ($request->has('username') || $request->has('password')) {
                $userData = [];
                if ($request->has('username')) {
                    $userData['username'] = $validated['username'];
                }
                if ($request->has('phone_no')) {
                    $userData['phone_no'] = $validated['phone_no'];
                }
                if ($request->has('email')) {
                    $userData['email'] = $validated['email'];
                }
                if ($request->has('password')) {
                    $userData['password'] = bcrypt($validated['password']);
                }
                $staff->user()->update($userData);
            }

            DB::commit();
            return new StaffResource($staff);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();
        return response()->json(null, 204);
    }

 // ... existing code ...
 public function storeBulk(Request $request)
 {
     DB::beginTransaction();
     try {
         $validated = $request->validate([
             'staff' => 'required|array|min:1',
             'staff.*.first_name' => 'required|string|max:100',
             'staff.*.last_name' => 'required|string|max:100',
             'staff.*.department' => 'required|string|max:100',
             'staff.*.phone_no' => 'nullable|string|max:100',
             'staff.*.email' => 'nullable|string|max:100',
             'staff.*.username' => 'required|string|unique:users',
             'staff.*.password' => 'required|string|min:4',
         ]);
 
         $user = $request->user();
         $libraryBranchId = $user->library_branch_id;
 
         $createdStaff = [];
         foreach ($validated['staff'] as $staffData) {
             $user = User::create([
                 'username' => $staffData['username'],
                 'password' => bcrypt($staffData['password']),
                 'email' => $staffData['email'] ?? null,
                 'phone_no' => $staffData['phone_no'] ?? null,
                 'library_branch_id' => $libraryBranchId,
             ]);
 
             $createdStaff[] = Staff::create([
                 'first_name' => $staffData['first_name'],
                 'last_name' => $staffData['last_name'],
                 'department' => $staffData['department'],
                 'user_id' => $user->id,
                 'library_branch_id' => $libraryBranchId,
             ]);
         }
 
         DB::commit();
         return StaffResource::collection(collect($createdStaff));
     } catch (\Exception $e) {
         DB::rollBack();
         return response()->json(['error' => $e->getMessage()], 500);
     }
 }

}