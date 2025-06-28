<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $authUser = Auth::user();
        $query = Student::with(['user', 'user.roles', 'grade', 'section'])
            ->whereHas('user', function ($q) use ($authUser) {
                $q->where('library_branch_id', $authUser->library_branch_id);
            });

        // Apply search filter if search term exists
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('username', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%");
                    })
                    ->orWhereHas('grade', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('section', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
            });
        }

        // Apply other filters
        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Sorting
        $sortField = $request->filled('sort_field') ? $request->sort_field : 'created_at';
        $sortOrder = $request->filled('sort_order') ? $request->sort_order : 'desc';
        $query->orderBy($sortField, $sortOrder);

        return StudentResource::collection(
            $query->paginate($request->per_page ?? 10)
        );
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'adress' => 'nullable|string|max:255',
                'grade_id' => 'required|exists:grades,id',
                'section_id' => 'required|exists:sections,id',
                'gender' => 'required|string|in:male,female,other',
                'phone_no' => 'required|string|max:20',
                'email' => 'required|email|max:100|unique:users,email',
                'username' => 'required|string|unique:users,username',
                'password' => 'required|string|min:8',
                // Removed 'role' validation since we assign it manually
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $authUser = $request->user();

            $user = User::create([
                'username' => $request->username,
                'password' => bcrypt($request->password),
                'email' => $request->email,
                'phone_no' => $request->phone_no,
                'library_branch_id' => $authUser->library_branch_id,
            ]);

            // Get the 'student' role and assign it
            $studentRole = Role::where('name', 'student')->first();
            if (!$studentRole) {
                throw new \Exception("Role 'student' not found.");
            }
            $user->assignRole($studentRole);

            $student = Student::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'adress' => $request->adress,
                'grade_id' => $request->grade_id,
                'section_id' => $request->section_id,
                'gender' => $request->gender,
                'user_id' => $user->id,
                'library_branch_id' => $authUser->library_branch_id,
            ]);

            DB::commit();

            return new StudentResource($student->load('user.roles', 'grade', 'section'));
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create student',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function show(Student $student)
    {
        return new StudentResource($student->load('user.roles', 'grade', 'section'));
    }


    public function update(Request $request, Student $student)
    {
        // Only allow admin or superadmin
        if (!auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins and superadmins can update students'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'first_name'   => 'sometimes|required|string|max:100',
                'last_name'    => 'sometimes|required|string|max:100',
                'adress'       => 'nullable|string|max:255',
                'grade_id'     => 'sometimes|required|exists:grades,id',
                'section_id'   => 'sometimes|required|exists:sections,id',
                'gender'       => 'sometimes|required|string|in:male,female,other',
                'phone_no'     => 'sometimes|required|string|max:20',
                'email'        => 'sometimes|required|email|max:100|unique:users,email,' . $student->user_id,
                'username'     => 'sometimes|required|string|unique:users,username,' . $student->user_id,
                'password'     => 'sometimes|string|min:8',
                // role removed from validation since we assign it manually
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update student fields
            $student->update($request->only([
                'first_name',
                'last_name',
                'adress',
                'grade_id',
                'section_id',
                'gender'
            ]));

            // Prepare user fields
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
                $student->user()->update($userData);
            }

            // Always assign 'student' role
            $studentRole = Role::where('name', 'student')->first();
            if (!$studentRole) {
                throw new \Exception("Role 'student' not found.");
            }
            $student->user->syncRoles([$studentRole]);

            DB::commit();

            return new StudentResource($student->fresh()->load('user.roles', 'grade', 'section'));
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Student $student)
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
            $user = $student->user;
            $student->delete();
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeBulk(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'students' => 'required|array|min:1',
                'students.*.first_name' => 'required|string|max:100',
                'students.*.last_name' => 'required|string|max:100',
                'students.*.adress' => 'nullable|string|max:255',
                'students.*.grade_id' => 'required|exists:grades,id',
                'students.*.section_id' => 'required|exists:sections,id',
                'students.*.gender' => 'required|string|in:male,female,other',
                'students.*.phone_no' => 'required|string|max:20',
                'students.*.email' => 'required|email|max:100|unique:users,email',
                'students.*.username' => 'required|string|unique:users,username',
                'students.*.password' => 'required|string|min:8',
                'students.*.role' => 'required|string|exists:roles,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $authUser = $request->user();
            $createdStudents = [];

            foreach ($request->students as $studentData) {
                $user = User::create([
                    'username' => $studentData['username'],
                    'password' => bcrypt($studentData['password']),
                    'email' => $studentData['email'],
                    'phone_no' => $studentData['phone_no'],
                    'library_branch_id' => $authUser->library_branch_id,
                ]);

                $user->assignRole($studentData['role']);

                $student = Student::create([
                    'first_name' => $studentData['first_name'],
                    'last_name' => $studentData['last_name'],
                    'adress' => $studentData['address'] ?? null,
                    'grade_id' => $studentData['grade_id'],
                    'section_id' => $studentData['section_id'],
                    'gender' => $studentData['gender'],
                    'user_id' => $user->id,
                    'library_branch_id' => $authUser->library_branch_id,
                ]);

                $createdStudents[] = $student;
            }

            DB::commit();

            return StudentResource::collection(
                collect($createdStudents)->load('user.roles', 'grade', 'section')
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create students in bulk',
                'error' => $e->getMessage()
            ], 500);
        }
    }
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
                'student_ids' => 'required|array|min:1',
                'student_ids.*' => 'exists:students,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $students = Student::whereIn('id', $request->student_ids)->get();

            foreach ($students as $student) {
                $user = $student->user;
                $student->delete();
                $user->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Students deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete students',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
