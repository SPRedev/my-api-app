<?php
// api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\RolePermissionController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// --- Standalone Login Route ---
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = App\Models\User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    // This part is now less important, as the app will rely on the /user endpoint,
    // but we can leave it for now.
    $permissions = $user->roles()->where('slug', 'admin')->exists() ? ['*'] : [];
    $token = $user->createToken('api-token', $permissions)->plainTextToken;

    return response()->json(['token' => $token]);
});

// --- Main Authenticated API v1 Group ---
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // --- Profile Management (For any authenticated user) ---
    Route::controller(ProfileController::class)->group(function () {
        Route::get('profile', 'show');
        Route::post('profile', 'update');
    });

    // --- User List for Assignments (For any authenticated user) ---
    Route::get('users-list', [UserController::class, 'list']);

    // --- Role & Permission Management ---
    // The 'can:manage-roles' middleware protects all routes in this group.
    Route::controller(RolePermissionController::class)->group(function () {
        Route::get('roles', 'getRoles');
        Route::post('roles', 'store');
        Route::delete('roles/{role}', 'destroy');
        Route::get('permissions', 'getPermissions');
        Route::post('roles/assign-permission', 'assignPermissionToRole');
        Route::post('roles/revoke-permission', 'revokePermissionFromRole');
    })->middleware('can:manage-roles');

    // --- Task Management ---
    Route::controller(TaskController::class)->group(function () {
        Route::get('tasks', 'index')->middleware('can:view-tasks');
        Route::get('tasks/{task}', 'show')->middleware('can:view-tasks');
        Route::post('tasks', 'store')->middleware('can:manage-tasks');
        Route::put('tasks/{task}', 'update')->middleware('can:manage-tasks');
        Route::patch('tasks/{task}', 'update')->middleware('can:manage-tasks');
        Route::delete('tasks/{task}', 'destroy')->middleware('can:manage-tasks');
        Route::get('statuses', 'getStatuses')->middleware('can:view-tasks');
        Route::get('priorities', 'getPriorities')->middleware('can:view-tasks');
    });

    // --- Full User Management ---
    // The 'can:manage-users' middleware protects all of these resource routes.
    Route::apiResource('users', UserController::class)->middleware('can:manage-users');

    // --- Project Management ---
    Route::apiResource('projects', ProjectController::class)->middleware('can:manage-projects');

    // UPDATED: GET CURRENT USER AND THEIR PERMISSIONS
    Route::get('/user', function (Request $request) {
        // Eager load the user's roles AND the permissions for each of those roles.
        $user = $request->user()->load('roles.permissions');

        // Create a flat, unique list of all permission slugs the user has.
        $permissions = $user->roles->flatMap(function ($role) {
            return $role->permissions->pluck('slug');
        })->unique()->values();

        // Return the user object, but also append the flat list of permissions.
        return response()->json([
            'user' => $user,
            'permissions' => $permissions,
        ]);
    });
});
