<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get all roles with permissions
     */
    public function getRoles()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        return response()->json(['data' => $roles]);
    }

    /**
     * Get all permissions
     */
    public function getPermissions()
    {
        $permissions = Permission::orderBy('name')->get();
        return response()->json(['data' => $permissions]);
    }

    /**
     * Create new role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles',
                Rule::notIn(['superadmin'])
            ],
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::create([
            'name' => strtolower($validated['name']),
            'guard_name' => 'api'
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'message' => 'Role created successfully',
            'data' => $role->load('permissions')
        ], 201);
    }

    /**
     * Update role
     */
    public function update(Request $request, Role $role)
    {
        if ($role->name === 'superadmin') {
            return response()->json([
                'message' => 'Superadmin role cannot be modified'
            ], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($role->id),
                Rule::notIn(['superadmin'])
            ],
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->update(['name' => strtolower($validated['name'])]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return response()->json([
            'message' => 'Role updated successfully',
            'data' => $role->load('permissions')
        ]);
    }

    /**
     * Delete role
     */
    public function destroy(Role $role)
    {
        if (in_array($role->name, ['superadmin', 'admin'])) {
            return response()->json([
                'message' => 'System roles cannot be deleted'
            ], 403);
        }

        if ($role->users()->exists()) {
            return response()->json([
                'message' => 'Cannot delete role assigned to users'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }
}