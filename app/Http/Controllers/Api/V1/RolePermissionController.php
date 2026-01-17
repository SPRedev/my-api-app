<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str; // 1. IMPORT THE STR CLASS

class RolePermissionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get all roles with their permissions.
     */
    public function getRoles()
    {
        return Role::with('permissions:id,name,slug')->get();
    }

    /**
     * Get all available permissions.
     */
    public function getPermissions()
    {
        return Permission::all();
    }

    /**
     * Create a new role.
     */
    public function store(Request $request)
    {
        // 2. ONLY validate the name. The slug will be generated automatically.
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        // 3. Create the role using the validated name and a generated slug.
        $role = Role::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']), // e.g., "New Role" -> "new-role"
        ]);

        return response()->json($role, 201); // Return the newly created role
    }

    /**
     * Delete a role.
     */
    public function destroy(Role $role)
    {
        // Optional: Add logic to prevent deletion of core roles like 'admin'
        if (in_array($role->slug, ['admin', 'user'])) {
            return response()->json(['message' => 'Cannot delete core system roles.'], 403);
        }

        $role->delete();

        return response()->json(null, 204); // Standard response for successful deletion
    }

    /**
     * Assign a specific permission to a role.
     */
    public function assignPermissionToRole(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::find($validated['role_id']);
        $role->permissions()->syncWithoutDetaching($validated['permission_id']);

        $permission = Permission::find($validated['permission_id']);
        return response()->json(['message' => "Permission '{$permission->name}' assigned to role '{$role->name}'."]);
    }

    /**
     * Revoke a specific permission from a role.
     */
    public function revokePermissionFromRole(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::find($validated['role_id']);
        $permission = Permission::find($validated['permission_id']);

        $role->permissions()->detach($permission);

        return response()->json(['message' => "Permission '{$permission->name}' revoked from role '{$role->name}'."]);
    }
}
