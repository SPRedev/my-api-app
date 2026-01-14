<?php
// api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

    // Give the admin user all permissions, and a regular user none for now
    $permissions = $user->email === 'admin@example.com' ? ['*'] : [];
    $token = $user->createToken('api-token', $permissions)->plainTextToken;

    return response()->json(['token' => $token]);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->load('roles:id,name'); // Also send back user's roles
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // --- User Management (Only for users who can 'manage-users') ---
    Route::apiResource('users', UserController::class)->middleware('can:manage-users');

    // --- Project Management ---
    Route::apiResource('projects', ProjectController::class)->middleware('can:manage-projects');

    // --- Task Management ---
    // Use a controller group for tasks
    Route::controller(TaskController::class)->group(function () {
        // Anyone who can 'view-tasks' can see the list and individual tasks
        Route::get('tasks', 'index')->middleware('can:view-tasks');
        Route::get('tasks/{task}', 'show')->middleware('can:view-tasks');

        // Only users who can 'manage-tasks' can create, update, or delete
        Route::post('tasks', 'store')->middleware('can:manage-tasks');
        Route::put('tasks/{task}', 'update')->middleware('can:manage-tasks');
        Route::patch('tasks/{task}', 'update')->middleware('can:manage-tasks');
        Route::delete('tasks/{task}', 'destroy')->middleware('can:manage-tasks');

        // Anyone who can 'view-tasks' can see available statuses and priorities
        Route::get('statuses', 'getStatuses')->middleware('can:view-tasks');
        Route::get('priorities', 'getPriorities')->middleware('can:view-tasks');
    });
});