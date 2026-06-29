<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Quochao56\Acl\Filament\Resources\RoleResource;
use Quochao56\Core\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Sync permissions from config/permissions.php to Database
        RoleResource::syncPermissionsToDatabase();

        // Clear Spatie cached permissions to ensure fresh load
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Create default roles
        $teacherRole = Role::findOrCreate('teacher', 'web');

        // 3. Setup default Super Admin
        $adminUser = User::where('email', 'thomaszen63@gmail.com')->first();
        if ($adminUser) {
            $adminUser->update(['is_super_admin' => true]);
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
