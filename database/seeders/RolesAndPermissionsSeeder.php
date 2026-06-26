<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Quochao56\Acl\Filament\Resources\RoleResource;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Sync permissions from config/permissions.php to Database
        RoleResource::syncPermissionsToDatabase();

        // Clear Spatie cached permissions to ensure fresh load
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Create default roles
        $superadminRole = Role::findOrCreate('superadmin', 'web');
        $teacherRole = Role::findOrCreate('teacher', 'web');

        // Superadmin gets all permissions in the system
        $categories = config('permissions.categories', []);
        $allPermissions = [];
        foreach ($categories as $perms) {
            $allPermissions = array_merge($allPermissions, array_keys($perms));
        }
        $superadminRole->syncPermissions($allPermissions);

        // 3. Assign Roles to default Users
        // Admin user (test@example.com) -> superadmin
        $adminUser = User::where('email', 'dtquynhnhu1026@gmail.com')->first();
        if ($adminUser) {
            $adminUser->assignRole($superadminRole);
        }

        // Teacher 1 (teacher1@example.com) -> teacher with all permissions (Teacher A)
        // $teacher1User = User::where('email', 'teacher1@example.com')->first();
        // if ($teacher1User) {
        //     $teacher1User->assignRole($teacherRole);
        //     $teacher1User->syncPermissions($allPermissions);
        // }

        // Teacher 2 (teacher2@example.com) -> teacher with limited permissions (Teacher B)
        // $teacher2User = User::where('email', 'teacher2@example.com')->first();
        // if ($teacher2User) {
        //     $teacher2User->assignRole($teacherRole);
        //     $teacher2User->syncPermissions([
        //         'manage_plans_evaluations',
        //         'view_progress_reports'
        //     ]);
        // }
    }
}
