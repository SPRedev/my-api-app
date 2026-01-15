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

    $permissions = $user->email === 'admin@example.com' ? ['*'] : [];
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

    // --- NEW: User List for Assignments (For any authenticated user) ---
    Route::get('users-list', [UserController::class, 'list']);

    // --- Role & Permission Management (Admins Only) ---
    Route::middleware('can:manage-roles')->controller(RolePermissionController::class)->group(function () {
        Route::get('roles', 'getRoles');
        Route::post('roles', 'store');
        Route::delete('roles/{role}', 'destroy');
        Route::get('permissions', 'getPermissions');
        Route::post('roles/assign-permission', 'assignPermissionToRole');
        Route::post('roles/revoke-permission', 'revokePermissionFromRole');
    });

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

    // --- Full User Management (Admins Only) ---
    Route::apiResource('users', UserController::class)->middleware('can:manage-users');

    // --- Project Management ---
    Route::apiResource('projects', ProjectController::class)->middleware('can:manage-projects');

    // GET CURRENT USER (can go anywhere in the group)
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles:id,name');
    });
});
