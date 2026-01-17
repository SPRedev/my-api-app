<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\QueryException; // 1. IMPORT QueryException
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function list()
    {
        return User::select('id', 'name')->get();
    }

    public function index()
    {
        return User::with('roles')->latest()->paginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->roles()->sync($validated['roles']);

        return response()->json($user->load('roles'), 201);
    }

    public function show(User $user)
    {
        return $user->load('roles');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['sometimes', 'required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->update($request->except('password', 'roles'));

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
            $user->save();
        }

        if ($request->has('roles')) {
            $user->roles()->sync($validated['roles']);
        }

        return response()->json($user->load('roles'));
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if (Auth::user()->id === $user->id) {
            return response()->json(['error' => 'You cannot delete your own account.'], 403);
        }

        // 2. WRAP the delete call in a try-catch block
        try {
            $user->delete();
            return response()->json(null, 204); // Success
        } catch (QueryException $e) {
            // 3. CATCH the specific database error
            // Check for a foreign key constraint violation (error code 1451 for MySQL)
            if ($e->errorInfo[1] == 1451) {
                // 4. RETURN a user-friendly 409 Conflict error
                return response()->json([
                    'error' => 'Cannot delete user. They are the creator of one or more tasks. Please reassign those tasks first.'
                ], 409); // 409 Conflict is the appropriate status code
            }

            // For any other database error, return a generic 500 error
            return response()->json(['error' => 'A database error occurred.'], 500);
        }
    }
}
