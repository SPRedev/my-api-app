<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
     * Assign a specific permission to a role.
     */
    public function assignPermissionToRole(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::find($validated['role_id']);

        // The only change is on the next line:
        $role->permissions()->syncWithoutDetaching($validated['permission_id']);

        $permission = Permission::find($validated['permission_id']); // We still need this for the message
        return response()->json(['message' => "Permission '{$permission->name}' assigned to role '{$role->name}'."]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'slug' => 'required|string|max:255|unique:roles,slug',
        ]);

        $role = Role::create($validated);

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

        // Use detach() to remove the permission.
        $role->permissions()->detach($permission);

        return response()->json(['message' => "Permission '{$permission->name}' revoked from role '{$role->name}'."]);
    }
}
