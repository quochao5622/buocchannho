<?php

use Quochao56\Student\Models\Student;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Models\StudentAssignment;

uses(\Quochao56\PlanningEvaluation\Tests\TestCase::class);

it('manages student assignments and maintains history correctly', function () {
    $student = new Student();
    $student->name = 'Student';
    $student->save();

    $teacherA = new Employee();
    $teacherA->name = 'Teacher A';
    $teacherA->save();

    $teacherB = new Employee();
    $teacherB->name = 'Teacher B';
    $teacherB->save();

    // 1. Assign teacher A
    $assignmentA = $student->assignments()->create([
        'employee_id' => $teacherA->id,
        'assigned_at' => now(),
    ]);

    expect($student->currentTeacher->id)->toBe($teacherA->id);
    expect($student->currentAssignment->employee_id)->toBe($teacherA->id);

    // 2. Assign teacher B (closes teacher A assignment)
    $unassignedTime = now();
    $student->assignments()
        ->whereNull('unassigned_at')
        ->update(['unassigned_at' => $unassignedTime]);

    $assignmentB = $student->assignments()->create([
        'employee_id' => $teacherB->id,
        'assigned_at' => $unassignedTime,
    ]);

    // Refresh relationships
    $student->unsetRelation('assignments');
    $student->unsetRelation('currentAssignment');
    $student->unsetRelation('currentTeacher');

    expect($student->currentTeacher->id)->toBe($teacherB->id);
    expect($student->currentAssignment->employee_id)->toBe($teacherB->id);

    // Verify history contains teacher A with unassigned_at
    $oldAssignment = $student->assignments()->where('employee_id', $teacherA->id)->first();
    expect($oldAssignment->unassigned_at)->not->toBeNull();
});
