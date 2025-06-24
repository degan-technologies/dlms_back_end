<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Http\Resources\StaffResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::with(['user', 'user.roles']);

        $authUser = auth('api')->user();

        if ($authUser) {
            // Superadmin: only show admin department staff
            if ($authUser->hasRole('superadmin')) {
                $query->whereRaw('LOWER(department) LIKE ?', ['%admin%']);
            }

            // Admin: exclude admin department, show only staff in same library_branch
            elseif ($authUser->hasRole('admin')) {
                $query->whereRaw('LOWER(department) NOT LIKE ?', ['%admin%'])
                    ->whereHas('user', function ($q) use ($authUser) {
                        $q->where('library_branch_id', $authUser->library_branch_id);
                    });
            }

            // Other roles can be handled if needed here
        }

        // Exclude users with superadmin role
        $query->whereHas('user.roles', function ($q) {
            $q->where('name', '!=', 'superadmin');
        });

        // Global search
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(first_name) LIKE ?', ["%$search%"])
                    ->orWhereRaw('LOWER(last_name) LIKE ?', ["%$search%"])
                    ->orWhereRaw('LOWER(department) LIKE ?', ["%$search%"])
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->whereRaw('LOWER(username) LIKE ?', ["%$search%"])
                            ->orWhereRaw('LOWER(email) LIKE ?', ["%$search%"]);
                    });
            });
        }

        // Column-specific filters
        if ($request->filled('first_name')) {
            $query->where('first_name', 'LIKE', '%' . $request->first_name . '%');
        }

        if ($request->filled('last_name')) {
            $query->where('last_name', 'LIKE', '%' . $request->last_name . '%');
        }

        if ($request->filled('department')) {
            $query->where('department', 'LIKE', '%' . $request->department . '%');
        }

        // Filter by specific role if provided
        if ($request->filled('role')) {
            $query->whereHas('user.roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Sorting
        $sortField = $request->filled('sortField') ? $request->sortField : 'created_at';
        $sortOrder = $request->filled('sortOrder') ? $request->sortOrder : 'desc';
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $perPage = $request->per_page ?? 10;
        $page = $request->page ?? 1;

        return StaffResource::collection($query->paginate($perPage, ['*'], 'page', $page));
    }


    public function store(Request $request)
    {
        DB::beginTransaction();

        $authUser = $request->user();
        $isSuperAdmin = $authUser->hasRole('superadmin');

        try {
            // Build base validation rules
            $validationRules = [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'department' => 'required|string|max:100',
                'phone_no' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100|unique:users,email',
                'username' => 'required|string|unique:users,username',
                'password' => 'required|string|min:8',
            ];

            // Add conditional fields
            if ($isSuperAdmin) {
                $validationRules['library_branch_id'] = 'required|exists:library_branches,id';
            } else {
                $validationRules['role'] = 'required|string|exists:roles,name';
            }

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Determine role to assign
            $roleToAssign = $isSuperAdmin ? 'admin' : $request->role;

            // Determine the library branch ID
            $libraryBranchId = $isSuperAdmin
                ? $request->library_branch_id
                : $authUser->library_branch_id;

            // Create the user
            $user = User::create([
                'username' => $request->username,
                'password' => bcrypt($request->password),
                'email' => $request->email,
                'phone_no' => $request->phone_no,
                'library_branch_id' => $libraryBranchId,
            ]);

            // Assign role to the user
            $user->assignRole($roleToAssign);

            // Create the staff
            $staff = Staff::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'department' => $request->department,
                'user_id' => $user->id,
                'library_branch_id' => $libraryBranchId,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff created successfully',
                'data' => new StaffResource($staff->load('user.roles'))
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create staff',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Staff $staff)
    {
        return response()->json([
            'success' => true,
            'data' => new StaffResource($staff->load('user.roles'))
        ]);
    }

    public function update(Request $request, Staff $staff)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins and superadmins can update staffs'
            ], 403);
        }
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|required|string|max:100',
                'last_name' => 'sometimes|required|string|max:100',
                'department' => 'sometimes|required|string|max:100',
                'phone_no' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100|unique:users,email,' . $staff->user_id,
                'username' => 'sometimes|required|string|unique:users,username,' . $staff->user_id,
                'password' => 'sometimes|string|min:8',
                'role' => 'sometimes|string|exists:roles,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $staff->update($request->only([
                'first_name',
                'last_name',
                'department'
            ]));

            $userData = [];
            if ($request->has('username')) {
                $userData['username'] = $request->username;
            }
            if ($request->has('phone_no')) {
                $userData['phone_no'] = $request->phone_no;
            }
            if ($request->has('email')) {
                $userData['email'] = $request->email;
            }
            if ($request->has('password')) {
                $userData['password'] = bcrypt($request->password);
            }

            if (!empty($userData)) {
                $staff->user()->update($userData);
            }

            // Update role if provided
            if ($request->has('role')) {
                $staff->user->syncRoles([$request->role]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff updated successfully',
                'data' => new StaffResource($staff->load('user.roles'))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update staff',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Staff $staff)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins and superadmins can update staffs'
            ], 403);
        }
        DB::beginTransaction();
        try {
            $user = $staff->user;
            $staff->delete();
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete staff',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Check if user has admin or superadmin role
    public function destroyBulk(Request $request)
    {
        // Check if user has admin or superadmin role
        if (!auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins and superadmins can delete students'
            ], 403);
        }
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'staff_ids' => 'required|array|min:1',
                'staff_ids.*' => 'exists:staff,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $staffIds = $request->staff_ids;
            $staffs = Staff::whereIn('id', $staffIds)->get();

            foreach ($staffs as $staff) {
                $user = $staff->user;
                $staff->delete();
                $user->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete staff',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function storeBulk(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'staff' => 'required|array|min:1',
                'staff.*.first_name' => 'required|string|max:100',
                'staff.*.last_name' => 'required|string|max:100',
                'staff.*.department' => 'required|string|max:100',
                'staff.*.phone_no' => 'nullable|string|max:20',
                'staff.*.email' => 'nullable|email|max:100|unique:users,email',
                'staff.*.username' => 'required|string|unique:users,username',
                'staff.*.password' => 'required|string|min:8',
                'staff.*.role' => 'required|string|exists:roles,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $authUser = $request->user();
            $createdStaff = [];

            foreach ($request->staff as $staffData) {
                $user = User::create([
                    'username' => $staffData['username'],
                    'password' => bcrypt($staffData['password']),
                    'email' => $staffData['email'] ?? null,
                    'phone_no' => $staffData['phone_no'] ?? null,
                    'library_branch_id' => $authUser->library_branch_id,
                ]);

                // Assign role to the user
                $user->assignRole($staffData['role']);

                $staff = Staff::create([
                    'first_name' => $staffData['first_name'],
                    'last_name' => $staffData['last_name'],
                    'department' => $staffData['department'],
                    'user_id' => $user->id,
                    'library_branch_id' => $authUser->library_branch_id,
                ]);

                $createdStaff[] = $staff;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk staff creation successful',
                'data' => StaffResource::collection(collect($createdStaff))
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create staff in bulk',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
