<?php

use App\Models\User;
use Quochao56\Student\Models\Student;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Models\StudentAssignment;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Sync permissions from config
    \Quochao56\Acl\Filament\Resources\RoleResource::syncPermissionsToDatabase();

    // Create roles
    $this->teacherRole = Role::findOrCreate('teacher', 'web');

    // Create Superadmin User
    $this->superadmin = User::factory()->create([
        'email' => 'superadmin@example.com',
        'is_super_admin' => true,
    ]);

    // Create Teachers
    $this->teacherA = User::factory()->create([
        'email' => 'teacherA@example.com'
    ]);
    $this->teacherA->assignRole($this->teacherRole);
    $this->teacherA->syncPermissions(['plannings.index', 'plannings.progress', 'students.index']);

    $this->employeeA = Employee::create([
        'email' => 'teacherA@example.com',
        'employee_code' => 'GV00A',
        'name' => 'Teacher A',
        'phone' => '111',
        'address' => 'A',
        'position' => 'Teacher',
        'employment_type' => 'full-time',
        'hired_at' => now(),
        'status' => \App\Enum\BaseStatusEnum::Active->value ?? \App\Enum\BaseStatusEnum::Active ?? 'active',
        'dob' => '1990-01-01',
        'gender' => 'male',
    ]);

    $this->teacherB = User::factory()->create([
        'email' => 'teacherB@example.com'
    ]);
    $this->teacherB->assignRole($this->teacherRole);
    $this->teacherB->syncPermissions(['plannings.index', 'plannings.progress', 'students.index']);

    $this->employeeB = Employee::create([
        'email' => 'teacherB@example.com',
        'employee_code' => 'GV00B',
        'name' => 'Teacher B',
        'phone' => '222',
        'address' => 'B',
        'position' => 'Teacher',
        'employment_type' => 'full-time',
        'hired_at' => now(),
        'status' => \App\Enum\BaseStatusEnum::Active->value ?? \App\Enum\BaseStatusEnum::Active ?? 'active',
        'dob' => '1990-01-01',
        'gender' => 'male',
    ]);

    // Create Students
    $this->student1 = Student::create([
        'student_code' => 'HS00A',
        'name' => 'Student of A',
        'status' => 'active',
    ]);

    $this->student2 = Student::create([
        'student_code' => 'HS00B',
        'name' => 'Student of B',
        'status' => 'active',
    ]);

    // Assign Student 1 -> Teacher A
    StudentAssignment::create([
        'student_id' => $this->student1->id,
        'employee_id' => $this->employeeA->id,
        'assigned_at' => now(),
    ]);

    // Assign Student 2 -> Teacher B
    StudentAssignment::create([
        'student_id' => $this->student2->id,
        'employee_id' => $this->employeeB->id,
        'assigned_at' => now(),
    ]);
});

it('scopes students query for teachers to only their assigned students', function () {
    // When logged in as Teacher A
    $this->actingAs($this->teacherA);

    $scopedStudents = \Quochao56\Student\Filament\Resources\StudentResource::getEloquentQuery()->pluck('id')->toArray();

    expect($scopedStudents)->toContain($this->student1->id);
    expect($scopedStudents)->not->toContain($this->student2->id);

    // When logged in as Teacher B
    $this->actingAs($this->teacherB);

    $scopedStudents = \Quochao56\Student\Filament\Resources\StudentResource::getEloquentQuery()->pluck('id')->toArray();

    expect($scopedStudents)->toContain($this->student2->id);
    expect($scopedStudents)->not->toContain($this->student1->id);
});

it('allows superadmin to see all students', function () {
    $this->actingAs($this->superadmin);

    $scopedStudents = \Quochao56\Student\Filament\Resources\StudentResource::getEloquentQuery()->pluck('id')->toArray();

    expect($scopedStudents)->toContain($this->student1->id);
    expect($scopedStudents)->toContain($this->student2->id);
});

it('enforces equipments.index permission to control access to equipment policy', function () {
    // Teacher A does not have 'equipments.index' permission
    $this->actingAs($this->teacherA);
    expect(Gate::allows('viewAny', \Quochao56\Equipment\Models\Equipment::class))->toBeFalse();

    // Superadmin has all permissions
    $this->actingAs($this->superadmin);
    expect(Gate::allows('viewAny', \Quochao56\Equipment\Models\Equipment::class))->toBeTrue();
});
