<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use Quochao56\PlanningEvaluation\Models\EvaluationHistory;
use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\PlanningEvaluation\Tests\TestCase;
use Quochao56\Student\Models\Student;

uses(TestCase::class, RefreshDatabase::class);

it('upserts evaluation from planning and maps details correctly', function () {
    $student = Student::create([
        'student_code' => 'HS01',
        'name' => 'Test Student',
        'status' => 'active',
    ]);

    $employee = Employee::create([
        'employee_code' => 'GV01',
        'name' => 'Test Teacher',
        'status' => 'active',
    ]);

    $planning = Planning::create([
        'name' => 'Tháng 1',
        'student_id' => $student->id,
        'employee_id' => $employee->id,
        'status' => 'published',
        'planning_details' => [
            [
                'linh_vuc' => [
                    ['content' => 'Vận động'],
                    ['content' => 'Nhận thức'],
                ],
                'muc_tieu' => [
                    ['content' => 'Đi bộ 10 bước'],
                    ['content' => 'Nhận biết màu đỏ'],
                ],
            ],
        ],
    ]);

    $evaluation = Evaluation::upsertFromPlanning($planning);

    expect($evaluation->planning_id)->toBe($planning->id);
    expect($evaluation->name)->toBe('Tháng 1');
    expect($evaluation->status->value)->toBe('draft');

    $details = $evaluation->evaluation_details;
    expect($details)->toBeArray();
    expect($details)->toHaveCount(1);

    expect($details[0]['linh_vuc'])->toBe("Vận động\nNhận thức");
    expect($details[0]['muc_tieu'])->toHaveCount(2);
    expect($details[0]['muc_tieu'][0]['content'])->toBe('Đi bộ 10 bước');
    expect($details[0]['muc_tieu'][0]['danh_gia'])->toBeNull();
    expect($details[0]['muc_tieu'][0]['nhan_xet'])->toBeNull();
});

it('removes tabs from evaluation details when saving', function () {
    $evaluation = new Evaluation;
    $evaluation->name = 'Test Eval';
    $evaluation->planning_id = 1; // Assuming it doesn't strictly check FK in the unit test unless constrained
    $evaluation->status = 'draft';
    $evaluation->evaluation_details = [
        [
            'linh_vuc' => "Vận\tđộng",
            'muc_tieu' => [
                ['content' => "Nhận biết\tmàu sắc", 'danh_gia' => "\tTốt\t"],
            ],
        ],
    ];
    $evaluation->save();

    $details = $evaluation->refresh()->evaluation_details;
    expect($details[0]['linh_vuc'])->toBe('Vận động');
    expect($details[0]['muc_tieu'][0]['content'])->toBe('Nhận biết màu sắc');
    expect($details[0]['muc_tieu'][0]['danh_gia'])->toBe(' Tốt ');
});

it('creates history snapshot when saved', function () {
    $evaluation = Evaluation::create([
        'name' => 'Initial Name',
        'planning_id' => 1,
        'status' => 'draft',
        'evaluation_details' => [],
    ]);

    // Update to trigger saved event again
    $evaluation->name = 'Updated Name';
    $evaluation->save();

    $histories = EvaluationHistory::where('evaluation_id', $evaluation->id)->get();

    // Should have 2 histories (created, and updated)
    expect($histories)->toHaveCount(2);
    expect($histories->last()->snapshot['name'])->toBe('Updated Name');
});
