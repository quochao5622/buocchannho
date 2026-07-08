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

        // 2. Define permission lists for each role
        $managerPermissions = [
            // Student
            'students.index',
            'students.create',
            'students.edit',
            'students.show',
            'students.destroy',
            'students.assign',
            'students.view_all',
            'student_leave_requests.index',
            'student_leave_requests.create',
            'student_leave_requests.edit',
            'student_leave_requests.destroy',
            'student_leave_requests.view_all',
            // Employee
            'employees.index',
            'employees.create',
            'employees.edit',
            'employees.show',
            'employees.destroy',
            'employee_attendances.index',
            'employee_attendances.create',
            'employee_attendances.edit',
            'employee_attendances.show',
            'employee_attendances.destroy',
            'employee_attendances.view_all',
            'employee_attendances.approve_flagged_location',
            'employee_attendances.reject_flagged_location',
            'employee_attendances.manage',
            'employee_attendances.view_overview',
            'leave_requests.index',
            'leave_requests.create',
            'leave_requests.edit',
            'leave_requests.destroy',
            'leave_requests.approve',
            'leave_requests.view_all',
            'attendance_correction_requests.index',
            'attendance_correction_requests.create',
            'attendance_correction_requests.edit',
            'attendance_correction_requests.show',
            'attendance_correction_requests.destroy',
            'attendance_correction_requests.approve',
            'attendance_correction_requests.reject',
            'attendance_correction_requests.view_all',
            // Session Log
            'daily_logs.index',
            'daily_logs.create',
            'daily_logs.edit',
            'daily_logs.show',
            'daily_logs.destroy',
            'behavior_incidents.index',
            'behavior_incidents.create',
            'behavior_incidents.edit',
            'behavior_incidents.show',
            'behavior_incidents.destroy',
            // Planning & Evaluation
            'plannings.index',
            'plannings.create',
            'plannings.edit',
            'plannings.show',
            'plannings.destroy',
            'plannings.export',
            'plannings.tracker',
            'plannings.progress',
            'plannings.view_all',
            'plannings.approve',
            'evaluations.index',
            'evaluations.create',
            'evaluations.edit',
            'evaluations.show',
            'evaluations.destroy',
            'evaluations.export',
            'evaluations.view_all',
            'evaluations.approve',
            // Equipment
            'equipments.index',
            'equipments.create',
            'equipments.edit',
            'equipments.show',
            'equipments.destroy',
            'equipment_categories.index',
            'equipment_categories.create',
            'equipment_categories.edit',
            'equipment_categories.show',
            'equipment_categories.destroy',
            'equipment_inventories.index',
            'equipment_inventories.create',
            'equipment_inventories.edit',
            'equipment_inventories.show',
            'equipment_inventories.destroy',
            'equipment_inventories.approve',
            // Scheduler
            'schedules.index',
            'schedules.create',
            'schedules.edit',
            'schedules.show',
            'schedules.destroy',
            // User Management
            'users.index',
            'users.create',
            'users.edit',
            'users.show',
            'users.destroy',
        ];

        $teacherLeadPermissions = [
            'students.index',
            'students.show',
            'students.view_all',
            'student_leave_requests.index',
            'student_leave_requests.create',
            'student_leave_requests.edit',
            'student_leave_requests.destroy',
            'student_leave_requests.view_all',
            'daily_logs.index',
            'daily_logs.create',
            'daily_logs.edit',
            'daily_logs.show',
            'daily_logs.destroy',
            'behavior_incidents.index',
            'behavior_incidents.create',
            'behavior_incidents.edit',
            'behavior_incidents.show',
            'behavior_incidents.destroy',
            'plannings.index',
            'plannings.create',
            'plannings.edit',
            'plannings.show',
            'plannings.export',
            'plannings.tracker',
            'plannings.progress',
            'plannings.view_all',
            'plannings.approve',
            'evaluations.index',
            'evaluations.create',
            'evaluations.edit',
            'evaluations.show',
            'evaluations.export',
            'evaluations.view_all',
            'evaluations.approve',
            'schedules.index',
            'schedules.show',
            'leave_requests.index',
            'leave_requests.create',
            'leave_requests.edit',
            'leave_requests.destroy',
            'employee_attendances.index',
            'employee_attendances.create',
            'attendance_correction_requests.index',
            'attendance_correction_requests.create',
        ];

        $teacherCollaboratorPermissions = [
            'students.index',
            'students.show',
            'student_leave_requests.index',
            'student_leave_requests.create',
            'student_leave_requests.edit',
            'student_leave_requests.destroy',
            'daily_logs.index',
            'daily_logs.create',
            'daily_logs.edit',
            'daily_logs.show',
            'behavior_incidents.index',
            'behavior_incidents.create',
            'behavior_incidents.edit',
            'behavior_incidents.show',
            'plannings.index',
            'plannings.create',
            'plannings.edit',
            'plannings.show',
            'plannings.export',
            'evaluations.index',
            'evaluations.create',
            'evaluations.edit',
            'evaluations.show',
            'evaluations.export',
            'schedules.index',
            'schedules.show',
            'leave_requests.index',
            'leave_requests.create',
            'leave_requests.edit',
            'leave_requests.destroy',
            'employee_attendances.index',
            'employee_attendances.create',
            'attendance_correction_requests.index',
            'attendance_correction_requests.create',
        ];

        // 3. Create default roles
        $managerRole = Role::findOrCreate('Quản lý', 'web');
        $teacherLeadRole = Role::findOrCreate('Giáo viên - Chủ quản', 'web');
        $teacherCollaboratorRole = Role::findOrCreate('Giáo viên - Cộng tác viên', 'web');

        // Sync role permissions
        $managerRole->syncPermissions($managerPermissions);
        $teacherLeadRole->syncPermissions($teacherLeadPermissions);
        $teacherCollaboratorRole->syncPermissions($teacherCollaboratorPermissions);

        // 4. Setup default Super Admin
        $adminUser = User::where('email', 'thomaszen63@gmail.com')->first();
        if ($adminUser) {
            $adminUser->update(['is_super_admin' => true]);
        } else {
            User::query()->firstOrCreate(
                ['email' => 'thomaszen63@gmail.com'],
                [
                    'name' => 'Admin',
                    'password' => bcrypt('password'),
                    'is_active' => true,
                    'is_super_admin' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        $testUser = User::where('email', 'test@example.com')->first();
        if ($testUser) {
            $testUser->update(['is_super_admin' => true, 'email_verified_at' => now(), 'is_active' => true]);
        }

        // Create a Manager User
        $managerUser = User::query()->firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Quản lý Hệ thống',
                'password' => bcrypt('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $managerUser->assignRole($managerRole);

        // Assign Roles to Teachers
        $teacher1User = User::where('email', 'teacher1@example.com')->first();
        if ($teacher1User) {
            $teacher1User->assignRole($teacherLeadRole);
            $teacher1User->update(['email_verified_at' => now()]);
        }

        $teacher2User = User::where('email', 'teacher2@example.com')->first();
        if ($teacher2User) {
            $teacher2User->assignRole($teacherCollaboratorRole);
            $teacher2User->update(['email_verified_at' => now()]);
        }
    }
}
