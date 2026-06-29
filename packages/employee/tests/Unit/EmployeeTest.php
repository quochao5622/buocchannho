<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Employee\Models\Employee;
use Quochao56\Employee\Tests\TestCase;
use Quochao56\PlanningEvaluation\Models\StudentAssignment;
use Quochao56\Student\Models\Student;

uses(TestCase::class, RefreshDatabase::class);

it('strips html tags from name', function () {
    $employee = new Employee;
    $employee->name = '<b>John</b> Doe <script>alert("xss")</script>';

    expect($employee->name)->toBe('John Doe alert("xss")');
});

it('has many students through assignments', function () {
    $employee = Employee::create([
        'employee_code' => 'GV_TEST',
        'name' => 'Teacher Test',
        'email' => 'teacher@test.com',
        'phone' => '123',
        'address' => 'abc',
        'position' => 'teacher',
        'employment_type' => 'full-time',
        'hired_at' => now(),
        'status' => BaseStatusEnum::Active->value,
        'dob' => '1990-01-01',
        'gender' => 'male',
    ]);

    $student = clone Student::create([
        'student_code' => 'HS_TEST',
        'name' => 'Student Test',
        'status' => 'active',
    ]);

    StudentAssignment::create([
        'student_id' => $student->id,
        'employee_id' => $employee->id,
        'assigned_at' => now(),
    ]);

    expect($employee->students()->count())->toBe(1);
    expect($employee->students()->first()->id)->toBe($student->id);
});
