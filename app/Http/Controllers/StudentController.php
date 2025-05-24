<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Resources\StudentResource;
use App\Models\Grade;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query()->with(['grade', 'section', 'user']);

        // Global search
        if ($request->filled('global')) {
            $search = $request->global;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('gender', 'like', "%$search%")
                  ->orWhere('phone_no', 'like', "%$search%")
                  ->orWhereHas('grade', function($q) use ($search) {
                      $q->where('name', 'like', "%$search%");
                  })
                  ->orWhereHas('section', function($q) use ($search) {
                      $q->where('name', 'like', "%$search%");
                  });
            });
        }

        // Individual field filters
        if ($request->filled('first_name')) {
            $query->where('first_name', 'like', '%' . $request->first_name . '%');
        }
        if ($request->filled('last_name')) {
            $query->where('last_name', 'like', '%' . $request->last_name . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('gender')) {
            $query->where('gender', 'like', '%' . $request->gender . '%');
        }
        if ($request->filled('phone_no')) {
            $query->where('phone_no', 'like', '%' . $request->phone_no . '%');
        }
        
        return $query->paginate($request->per_page ?? 10);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'adress' => 'nullable|string|max:255',
                'grade_name' => 'required|string|max:100',
                'section_name' => 'nullable|string|max:100',
                'phone_no' => 'nullable|string|max:15',
                'email' => 'nullable|email|max:255',
                'gender' => 'required|string',
                'username' => 'required|string|unique:users',
                'password' => 'required|string|min:4',
            ]);
        
            $authUser = $request->user();
            
            // Create or find grade
            $grade = Grade::Create(['name' => $validated['grade_name']]);
            
            // Create or find section if provided
            $section = null;
            if (!empty($validated['section_name'])) {
                $section = Section::Create([
                    'name' => $validated['section_name'],
                    'grade_id' => $grade->id
                ]);
            }
            
            $user = User::create([
                'username' => $validated['username'],
                'password' => bcrypt($validated['password']),
                'email' => $validated['email'] ?? null,
                'phone_no' => $validated['phone_no'] ?? null,
                'library_branch_id' => $authUser->library_branch_id,
            ]);
        
            $student = Student::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'adress' => $validated['adress'] ?? null,
                'grade_id' => $grade->id,
                'section_id' => $section->id ?? null,
                'gender' => $validated['gender'],
                'user_id' => $user->id,
            ]);
        
            DB::commit();
            return new StudentResource($student);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function batchStore(Request $request)
    {
        DB::beginTransaction();
        try {
            $studentsData = $request->input('students', []);
            $created = [];
            $authUser = $request->user();
            
            foreach ($studentsData as $data) {
                $validated = validator($data, [
                    'first_name' => 'required|string|max:100',
                    'last_name' => 'required|string|max:100',
                    'adress' => 'nullable|string|max:255',
                    'grade_name' => 'required|string|max:100',
                    'section_name' => 'nullable|string|max:100',
                    'phone_no' => 'nullable|string|max:15',
                    'email' => 'nullable|email|max:255',
                    'gender' => 'required|string',
                    'username' => 'required|string|unique:users',
                    'password' => 'required|string|min:4',
                ])->validate();
                
                // Create or find grade
                $grade = Grade::Create(['name' => $validated['grade_name']]);
                
                // Create or find section if provided
                $section = null;
                if (!empty($validated['section_name'])) {
                    $section = Section::Create([
                        'name' => $validated['section_name'],
                        'grade_id' => $grade->id
                    ]);
                }
                
                $user = User::create([
                    'username' => $validated['username'],
                    'password' => bcrypt($validated['password']),
                    'email' => $validated['email'] ?? null,
                    'phone_no' => $validated['phone_no'] ?? null,
                    'library_branch_id' => $authUser->library_branch_id,
                ]);
                
                $student = Student::create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'adress' => $validated['adress'] ?? null,
                    'grade_id' => $grade->id,
                    'section_id' => $section->id ?? null,
                    'gender' => $validated['gender'],
                    'user_id' => $user->id,
                ]);
                $created[] = $student;
            }
            
            DB::commit();
            return StudentResource::collection($created);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function show($id)
    {
        $student = Student::findOrFail($id);
        return new StudentResource($student);
    }

    public function update(Request $request, $id) 
    { 
        $student = Student::findOrFail($id);
    
        $validated = $request->validate([ 
            'first_name' => 'required|string|max:100', 
            'last_name' => 'required|string|max:100', 
            'adress' => 'nullable|string|max:255', 
            'grade_name' => 'nullable|string|max:100', 
            'section_name' => 'nullable|string|max:100', 
            'phone_no' => 'nullable|string|max:15', 
            'email' => 'nullable|email|max:255', 
            'gender' => 'required|string', 
            'username' => 'string|unique:users,username,'.$student->user_id, 
            'password' => 'string|min:4', 
        ]);
    
        // Handle grade update if provided
        $grade_id = $student->grade_id;
        if (!empty($validated['grade_name'])) {
            $grade = Grade::where('name', $validated['grade_name'])->first();
            if (!$grade) {
                $grade = Grade::create(['name' => $validated['grade_name']]);
            }
            $grade_id = $grade->id;
        }
    
        // Handle section update if provided
        $section_id = $student->section_id;
        if (!empty($validated['section_name'])) {
            $section = Section::where('name', $validated['section_name'])->where('grade_id', $grade_id)->first();
            if (!$section) {
                $section = Section::create([
                    'name' => $validated['section_name'],
                    'grade_id' => $grade_id
                ]);
            }
            $section_id = $section->id;
        }
    
        // Update student
        $student->update([ 
            'first_name' => $validated['first_name'], 
            'last_name' => $validated['last_name'], 
            'adress' => $validated['adress'] ?? null, 
            'grade_id' => $grade_id, 
            'section_id' => $section_id, 
            'gender' => $validated['gender'], 
        ]);
    
        // Update user if needed
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
            $student->user()->update($userData);
        }
    
        return new StudentResource($student);
    }
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        return response()->json(null, 204);
    }
}
