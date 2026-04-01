<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class CompanyRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions based on your requirements
        $permissions = [

            // Employees Management
            'view employees',
            'manage employees status',

            // Department Management
            'view departments',
            'create departments',
            'edit departments',
            'delete departments',
            'view departments users',

            // Campaign/Season Management
            'view campaigns',
            'create campaigns',
            'edit campaigns',
            'delete campaigns',
            'manage campaigns status',

            // Sessions Management
            'view sessions',
            'create sessions',
            'edit sessions',
            'delete sessions',
            'import sessions',

            // Import Management
            'manage imports',

            // Step Management
            'view steps',
            'create steps',
            'edit steps',
            'delete steps',

            // Quiz Management
            'view quiz',
            'create quiz',
            'edit quiz',
            'delete quiz',
            'view quiz attempted users',
            'export quiz attempted users',

            // Challenges Management
            'view challenges',
            'create challenges',
            'edit challenges',
            'delete challenges',
            'view challenges attempted users',
            'export challenges attempted users',
            'view challenges user requests',
            'manage challenges user requests',

            // SpinWheel Management
            'view spinwheel',
            'create spinwheel',
            'edit spinwheel',
            'delete spinwheel',
            'view spinwheel attempted users',
            'export spinwheel attempted users',

            // Survey/Feedback Management
            'view survey feedback',
            'create survey feedback',
            'edit survey feedback',
            'delete survey feedback',
            'view survey feedback attempted users',
            'export survey feedback attempted users',

            // Inspiration Challenges Management
            'view inspiration challenges',
            'create inspiration challenges',
            'edit inspiration challenges',
            'delete inspiration challenges',
            'view inspiration challenges attempted users',
            'view inspiration challenges user requests',
            'manage inspiration challenges user requests',
            'manage inspiration challenges import',
            'manage inspiration challenges export',

            // News Feed Management
            'view news feeds',
            'create news feeds',
            'edit news feeds',
            'delete news feeds',
            'manage news feeds status',

            // Rewards Management
            'view rewards',
            'give rewards',
            'create custom rewards',
            'edit custom rewards',
            'view custom rewards',
            'manage custom rewards status',

            // Gallery Management
            'view gallery',
            'create gallery',
            'delete gallery',

            // Posts Management
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'manage posts status',

            // Post Reports Management
            'view posts reports',
            'manage posts reports',
            'view reported users',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // ===== COMPANY ADMIN ROLE =====
        // Has complete access to everything
        $company_admin_role = Role::firstOrCreate([
            'name' => 'Company Admin',
            'guard_name' => 'web'
        ]);

        // Sync all web guard permissions to Company Admin role
        $company_admin_permissions = Permission::where('guard_name', 'web')->get();
        $company_admin_role->syncPermissions($company_admin_permissions);

        // Update all existing Company Admin users to have these permissions
        $company_admins = User::where('is_admin', true)->get();
        foreach ($company_admins as $admin_user) {
            $admin_user->assignRole($company_admin_role);
            $admin_user->syncPermissions($company_admin_permissions);
        }

        // ===== MANAGER ROLE =====
        // Can manage most operations except critical deletions and user management
        $manager_role = Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => 'web'
        ]);
        $manager_role->syncPermissions([

            // Employees Management
            'view employees',
            'manage employees status',

            // Department Management
            'view departments',
            'create departments',
            'edit departments',
            'view departments users',

            // Campaign/Season Management
            'view campaigns',
            'create campaigns',
            'manage campaigns status',

            // Sessions Management
            'view sessions',
            'create sessions',
            'edit sessions',
            'import sessions',

            // Import Management
            'manage imports',

            // Step Management
            'view steps',
            'create steps',
            'edit steps',

            // Quiz Management
            'view quiz',
            'create quiz',
            'edit quiz',
            'view quiz attempted users',
            'export quiz attempted users',

            // Challenges Management
            'view challenges',
            'create challenges',
            'edit challenges',
            'view challenges attempted users',
            'export challenges attempted users',
            'view challenges user requests',
            'manage challenges user requests',

            // SpinWheel Management
            'view spinwheel',
            'create spinwheel',
            'edit spinwheel',
            'view spinwheel attempted users',
            'export spinwheel attempted users',

            // Survey/Feedback Management
            'view survey feedback',
            'create survey feedback',
            'edit survey feedback',
            'view survey feedback attempted users',
            'export survey feedback attempted users',

            // Inspiration Challenges Management
            'view inspiration challenges',
            'create inspiration challenges',
            'edit inspiration challenges',
            'view inspiration challenges attempted users',
            'view inspiration challenges user requests',
            'manage inspiration challenges user requests',
            'manage inspiration challenges import',
            'manage inspiration challenges export',

            // News Feed Management
            'view news feeds',
            'create news feeds',
            'edit news feeds',

            // Rewards Management
            'view rewards',
            'give rewards',
            'create custom rewards',
            'edit custom rewards',
            'view custom rewards',
            'manage custom rewards status',

            // Gallery Management
            'view gallery',
            'create gallery',

            // Posts Management
            'view posts',
            'create posts',
            'edit posts',
            'manage posts status',

            // Post Reports Management
            'view posts reports',
            'manage posts reports',
            'view reported users',
        ]);

        // Update all existing Manager users to have these permissions
        $managers = User::whereRole('Manager')->get();
        foreach ($managers as $manager_user) {
            $manager_user->syncPermissions($manager_role->permissions);
        }

        // ===== VIEWER ROLE =====
        // Primarily view-only access with some basic management capabilities
        $viewer_role = Role::firstOrCreate([
            'name' => 'Viewer',
            'guard_name' => 'web'
        ]);
        $viewer_role->syncPermissions([

            // Employees Management
            'view employees',

            // Department Management
            'view departments',
            'view departments users',

            // Campaign/Season Management
            'view campaigns',

            // Sessions Management
            'view sessions',

            // Step Management
            'view steps',

            // Quiz Management
            'view quiz',
            'view quiz attempted users',

            // Challenges Management
            'view challenges',
            'view challenges attempted users',
            'view challenges user requests',

            // SpinWheel Management
            'view spinwheel',
            'view spinwheel attempted users',

            // Survey/Feedback Management
            'view survey feedback',
            'view survey feedback attempted users',

            // Inspiration Challenges Management
            'view inspiration challenges',
            'view inspiration challenges attempted users',
            'view inspiration challenges user requests',

            // News Feed Management
            'view news feeds',

            // Rewards Management
            'view rewards',
            'view custom rewards',

            // Gallery Management
            'view gallery',

            // Posts Management
            'view posts',

            // Post Reports Management
            'view posts reports',
            'view reported users',
        ]);

        // Update all existing Viewer users to have these permissions
        $viewers = User::whereRole('Viewer')->get();
        foreach ($viewers as $viewer_user) {
            $viewer_user->syncPermissions($viewer_role->permissions);
        }
    }
}
