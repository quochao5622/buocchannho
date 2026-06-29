<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\PlanningEvaluation\Models\PlanningHistory;
use Quochao56\PlanningEvaluation\Tests\TestCase;
use Quochao56\Student\Models\Student;

uses(TestCase::class, RefreshDatabase::class);

it('removes tabs from planning details when saving', function () {
    $student = Student::create([
        'student_code' => 'HS_P1',
        'name' => 'Student',
    ]);

    $employee = Employee::create([
        'employee_code' => 'GV_P1',
        'name' => 'Teacher',
    ]);

    $planning = new Planning;
    $planning->name = 'Test Planning Tabs';
    $planning->student_id = $student->id;
    $planning->employee_id = $employee->id;
    $planning->status = 'draft';
    $planning->planning_details = [
        [
            'linh_vuc' => [
                ['content' => "Lĩnh\tvực\t1"],
            ],
            'muc_tieu' => [
                ['content' => "Mục tiêu\t1"],
            ],
        ],
    ];
    $planning->save();

    $details = $planning->refresh()->planning_details;
    expect($details[0]['linh_vuc'][0]['content'])->toBe('Lĩnh vực 1');
    expect($details[0]['muc_tieu'][0]['content'])->toBe('Mục tiêu 1');
});

it('creates history snapshot when saved', function () {
    $student = Student::create([
        'student_code' => 'HS_P2',
        'name' => 'Student 2',
    ]);

    $employee = Employee::create([
        'employee_code' => 'GV_P2',
        'name' => 'Teacher 2',
    ]);

    $planning = Planning::create([
        'name' => 'Original Name',
        'student_id' => $student->id,
        'employee_id' => $employee->id,
        'status' => 'draft',
        'planning_details' => [],
    ]);

    // Trigger update
    $planning->name = 'New Name';
    $planning->save();

    $histories = PlanningHistory::where('planning_id', $planning->id)->get();

    // Created + Updated = 2
    expect($histories)->toHaveCount(2);
    expect($histories->last()->snapshot['name'])->toBe('New Name');
});
