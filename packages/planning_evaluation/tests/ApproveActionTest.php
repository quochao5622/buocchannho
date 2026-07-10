<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Filament\Actions\ApproveAction;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\PlanningEvaluation\Tests\TestCase;
use Quochao56\Student\Models\Student;
use Spatie\Permission\Models\Permission;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->student = Student::create([
        'student_code' => 'HS_TEST',
        'name' => 'Test Student',
    ]);

    $this->employee = Employee::create([
        'employee_code' => 'GV_TEST',
        'name' => 'Test Teacher',
    ]);

    Permission::findOrCreate('plannings.approve', 'web');
    Permission::findOrCreate('evaluations.approve', 'web');
});

it('can approve planning and set status to published', function () {
    $planning = Planning::create([
        'name' => 'Test Planning',
        'student_id' => $this->student->id,
        'employee_id' => $this->employee->id,
        'status' => BaseStatusEnum::Draft,
    ]);

    $action = ApproveAction::make();
    $action->handle($planning);

    expect($planning->refresh()->status)->toBe(BaseStatusEnum::Published);
});

it('can approve evaluation only if all goals are evaluated', function () {
    $planning = Planning::create([
        'name' => 'Test Planning 2',
        'student_id' => $this->student->id,
        'employee_id' => $this->employee->id,
        'status' => BaseStatusEnum::Published,
    ]);

    $evaluation = Evaluation::create([
        'name' => 'Test Evaluation',
        'planning_id' => $planning->id,
        'status' => BaseStatusEnum::Draft,
        'evaluation_details' => [
            [
                'linh_vuc' => 'Lĩnh vực 1',
                'muc_tieu' => [
                    [
                        'content' => 'Mục tiêu 1',
                        'danh_gia' => null,
                    ],
                ],
            ],
        ],
    ]);

    $action = ApproveAction::make();
    $action->handle($evaluation);

    expect($evaluation->refresh()->status)->toBe(BaseStatusEnum::Draft);

    $evaluation->update([
        'evaluation_details' => [
            [
                'linh_vuc' => 'Lĩnh vực 1',
                'muc_tieu' => [
                    [
                        'content' => 'Mục tiêu 1',
                        'danh_gia' => 'Đạt',
                    ],
                ],
            ],
        ],
    ]);

    $action->handle($evaluation);
    expect($evaluation->refresh()->status)->toBe(BaseStatusEnum::Published);
});
