<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions based on your requirements
        $permissions = [

            // Citizens Management
            'view citizens',
            'delete citizens',
            'manage citizens status',

            // Company Management
            'view companies',
            'view companies users',
            'delete companies users',
            'create companies',
            'edit companies',
            'delete companies',

            // Department Management
            'view departments',
            'create departments',
            'edit departments',
            'delete departments',

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

            // News Category Management
            // 'view news categories',
            // 'create news categories',
            // 'edit news categories',
            // 'delete news categories',

            // News Feed Management
            // 'view news feeds',
            // 'create news feeds',
            // 'edit news feeds',
            // 'delete news feeds',
            // 'manage news feeds status',

            // Rewards Management
            'view rewards',
            'give rewards',
            'create custom rewards',
            'edit custom rewards',
            'view custom rewards',
            'manage custom rewards status',

            // Contact Requests Management
            'view contact requests',

            // Gallery Management
            'view gallery',
            'create gallery',
            'delete gallery',

            // Notifications Management
            'view notifications',
            'create notifications',
            'view recipients',

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

            // Carbon Assessments Management
            'view carbon assessments',
            'export carbon assessments',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin'
            ]);
        }

        // ===== ADMIN ROLE =====
        // Has complete access to everything
        $admin = Admin::find(1);

        // Create Admin role if it doesn't exist
        $admin_role = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'admin'
        ]);

        // Sync all admin guard permissions to Admin role
        $admin_permissions = Permission::where('guard_name', 'admin')->get();
        $admin_role->syncPermissions($admin_permissions);

        // Assign role and permissions to admin user
        if ($admin) {
            $admin->assignRole('Admin');
            $admin->syncPermissions($admin_permissions); // Use filtered permissions
        }

        // ===== MANAGER ROLE =====
        // Can manage most operations except critical deletions and user management
        $manager_role = Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => 'admin'
        ]);
        $manager_role->syncPermissions([

            // Citizens Management
            'view citizens',
            'manage citizens status',

            // Company Management
            'create companies',
            'view companies',
            'view companies users',
            'edit companies',

            // Department Management
            'view departments',
            'create departments',
            'edit departments',

            // Campaign/Season Management
            'view campaigns',
            'create campaigns',
            'edit campaigns',
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

            // News Category Management
            // 'view news categories',
            // 'create news categories',
            // 'edit news categories',

            // News Feed Management
            // 'view news feeds',
            // 'create news feeds',
            // 'edit news feeds',
            // 'manage news feeds status',

            // Rewards Management
            'view rewards',
            'give rewards',
            'create custom rewards',
            'edit custom rewards',
            'view custom rewards',
            'manage custom rewards status',

            // Contact Requests Management
            'view contact requests',

            // Gallery Management
            'view gallery',
            'create gallery',

            // Notifications Management
            'view notifications',
            'create notifications',
            'view recipients',

            // Posts Management
            'view posts',
            'create posts',
            'edit posts',
            'manage posts status',

            // Post Reports Management
            'view posts reports',
            'manage posts reports',
            'view reported users',

            // Carbon Assessments Management
            'view carbon assessments',
            'export carbon assessments',
        ]);

        // ===== VIEWER ROLE =====
        // Primarily view-only access with some basic management capabilities
        $viewer_role = Role::firstOrCreate([
            'name' => 'Viewer',
            'guard_name' => 'admin'
        ]);
        $viewer_role->syncPermissions([

            // Citizens Management
            'view citizens',

            // Company Management
            'view companies',
            'view companies users',

            // Department Management
            'view departments',

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

            // News Category Management
            // 'view news categories',

            // News Feed Management
            // 'view news feeds',

            // Rewards Management
            'view rewards',
            'view custom rewards',

            // Contact Requests Management
            'view contact requests',

            // Gallery Management
            'view gallery',

            // Notifications Management
            'view notifications',
            'view recipients',

            // Posts Management
            'view posts',

            // Post Reports Management
            'view posts reports',
            'view reported users',

            // Carbon Assessments Management
            'view carbon assessments',
        ]);
    }
}
