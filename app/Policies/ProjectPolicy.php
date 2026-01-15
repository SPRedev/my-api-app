<?php
// In app/Policies/ProjectPolicy.php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Give admins god-mode. This runs before any other method in the policy.
     */
    public function before(User $user, string $ability): bool|null
    {
        // If the user has the 'admin' role slug, they can do anything.
        if ($user->roles()->where('slug', 'admin')->exists()) {
            return true;
        }
        return null; // Let the other policy methods decide.
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        // A user can update a project if they created it.
        return $user->id === $project->created_by;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        // A user can delete a project if they created it.
        return $user->id === $project->created_by;
    }
}
