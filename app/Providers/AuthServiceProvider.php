<?php

// In app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Project;
use App\Policies\ProjectPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
         Project::class => ProjectPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Check if the permissions table exists before trying to query it.
        // This prevents errors when running migrations on a fresh database.
        if (Schema::hasTable('permissions')) {
            // Dynamically register a gate for each permission.
            // The gate will check if the user's roles have that permission.
            Permission::all()->each(function ($permission) {
                Gate::define($permission->slug, function (User $user) use ($permission) {
                    return $user->roles()->whereHas('permissions', function ($query) use ($permission) {
                        $query->where('slug', $permission->slug);
                    })->exists();
                });
            });
        }

        // Define a "Super Admin" Gate.
        // This gate will automatically grant all permissions to users with the 'admin' role.
        Gate::before(function (User $user, $ability) {
            if ($user->roles()->where('slug', 'admin')->exists()) {
                return true;
            }
        });
    }
}
