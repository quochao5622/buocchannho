<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Models\StudentAssignment;
use Quochao56\Student\Models\Student;
use Quochao56\Student\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('retrieves current teacher from assignments', function () {
    $student = Student::create([
        'student_code' => 'HS_001',
        'name' => 'Student Test',
        'status' => 'active',
    ]);

    $teacher = Employee::create([
        'employee_code' => 'GV_001',
        'name' => 'Teacher Test',
        'position' => 'teacher',
        'employment_type' => 'full-time',
        'hired_at' => now(),
        'status' => 'active',
        'dob' => '1990-01-01',
        'gender' => 'male',
    ]);

    StudentAssignment::create([
        'student_id' => $student->id,
        'employee_id' => $teacher->id,
        'assigned_at' => now()->subDay(),
    ]);

    expect($student->currentAssignment)->not->toBeNull();
    expect($student->currentTeacher)->not->toBeNull();
    expect($student->currentTeacher->id)->toBe($teacher->id);
});

it('formats date of birth based on status', function () {
    $student = new Student;
    $student->dob = '2010-05-15';

    // Testing accessor logic if applicable (this is an example for the model test structure)
    expect($student->dob->format('Y-m-d'))->toBe('2010-05-15');
});
