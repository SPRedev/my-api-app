<?php
// In database/seeders/RoleAndPermissionSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        Role::truncate();
        DB::table('role_user')->truncate();
        DB::table('permission_role')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Define all permissions
        $permissions = [
            // User Permissions
            ['name' => 'Manage All Users', 'slug' => 'manage-users'], // For create, update, delete
            ['name' => 'View Users', 'slug' => 'view-users'],

            // Project Permissions
            ['name' => 'Manage All Projects', 'slug' => 'manage-projects'],
            ['name' => 'View Projects', 'slug' => 'view-projects'],

            // Task Permissions
            ['name' => 'Manage All Tasks', 'slug' => 'manage-tasks'],
            ['name' => 'View Tasks', 'slug' => 'view-tasks'],
            ['name' => 'Assign Tasks', 'slug' => 'assign-tasks'],
            ['name' => 'Comment on Tasks', 'slug' => 'comment-tasks'], // Future-proofing!
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
        $this->command->info('Permissions have been created.');

        // --- Create Roles and Assign Permissions ---

        // Admin Role (has all permissions)
        $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        // The Gate::before in AuthServiceProvider will handle giving admin all access,
        // but syncing them here is also good practice for clarity.
        $adminRole->permissions()->sync(Permission::all());
        $this->command->info('Administrator role created and assigned all permissions.');

        // Project Manager Role (can manage projects and tasks, but not users)
        $managerRole = Role::create(['name' => 'Project Manager', 'slug' => 'manager']);
        $managerPermissions = Permission::whereIn('slug', [
            'view-users', 'manage-projects', 'view-projects', 'manage-tasks', 'view-tasks', 'assign-tasks'
        ])->get();
        $managerRole->permissions()->sync($managerPermissions);
        $this->command->info('Project Manager role created.');

        // Basic User Role (can only view items and maybe comment)
        $userRole = Role::create(['name' => 'User', 'slug' => 'user']);
        $userPermissions = Permission::whereIn('slug', ['view-projects', 'view-tasks', 'comment-tasks'])->get();
        $userRole->permissions()->sync($userPermissions);
        $this->command->info('Basic User role created.');
          $this->command->info('Creating default users...');

        // Create Admin User
        $adminUser = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Use a secure password
        ]);

        // Assign the 'Administrator' role to the new admin user
        $adminUser->roles()->sync($adminRole);

        $this->command->info('Admin user created and assigned Administrator role.');
    
    }
}
