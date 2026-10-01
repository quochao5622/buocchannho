<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Core\Models\User;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Filament\Actions\ReopenAction;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\PlanningEvaluation\Tests\TestCase;
use Quochao56\Student\Models\Student;
use Spatie\Permission\Models\Permission;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->student = Student::create([
        'student_code' => 'HS_TEST_REOPEN',
        'name' => 'Test Student Reopen',
    ]);

    $this->employee = Employee::create([
        'employee_code' => 'GV_TEST_REOPEN',
        'name' => 'Test Teacher Reopen',
    ]);

    Permission::findOrCreate('plannings.reopen', 'web');
    Permission::findOrCreate('evaluations.reopen', 'web');
});

it('can reopen planning and set status back to draft', function () {
    $planning = Planning::create([
        'name' => 'Published Planning',
        'student_id' => $this->student->id,
        'employee_id' => $this->employee->id,
        'status' => BaseStatusEnum::Published,
    ]);

    $action = ReopenAction::make();
    $action->handle($planning);

    expect($planning->refresh()->status)->toBe(BaseStatusEnum::Draft);
});

it('can reopen evaluation and set status back to draft', function () {
    $planning = Planning::create([
        'name' => 'Planning for Evaluation',
        'student_id' => $this->student->id,
        'employee_id' => $this->employee->id,
        'status' => BaseStatusEnum::Published,
    ]);

    $evaluation = Evaluation::create([
        'name' => 'Published Evaluation',
        'planning_id' => $planning->id,
        'status' => BaseStatusEnum::Published,
    ]);

    $action = ReopenAction::make();
    $action->handle($evaluation);

    expect($evaluation->refresh()->status)->toBe(BaseStatusEnum::Draft);
});

it('respects planning policy reopen authorization', function () {
    $user = clone User::factory()->create(['is_super_admin' => false]);

    $publishedPlanning = Planning::create([
        'name' => 'Published Planning Policy',
        'student_id' => $this->student->id,
        'employee_id' => $this->employee->id,
        'status' => BaseStatusEnum::Published,
    ]);

    $draftPlanning = Planning::create([
        'name' => 'Draft Planning Policy',
        'student_id' => $this->student->id,
        'employee_id' => $this->employee->id,
        'status' => BaseStatusEnum::Draft,
    ]);

    expect(Gate::forUser($user)->allows('reopen', $publishedPlanning))->toBeFalse();

    $user->givePermissionTo('plannings.reopen');
    expect(Gate::forUser($user)->allows('reopen', $publishedPlanning))->toBeTrue();
    expect(Gate::forUser($user)->allows('reopen', $draftPlanning))->toBeFalse();
});

it('respects evaluation policy reopen authorization', function () {
    $user = clone User::factory()->create(['is_super_admin' => false]);

    $planning = Planning::create([
        'name' => 'Planning for Eval Policy',
        'student_id' => $this->student->id,
        'employee_id' => $this->employee->id,
        'status' => BaseStatusEnum::Published,
    ]);

    $publishedEvaluation = Evaluation::create([
        'name' => 'Published Evaluation Policy',
        'planning_id' => $planning->id,
        'status' => BaseStatusEnum::Published,
    ]);

    $draftEvaluation = Evaluation::create([
        'name' => 'Draft Evaluation Policy',
        'planning_id' => $planning->id,
        'status' => BaseStatusEnum::Draft,
    ]);

    expect(Gate::forUser($user)->allows('reopen', $publishedEvaluation))->toBeFalse();

    $user->givePermissionTo('evaluations.reopen');
    expect(Gate::forUser($user)->allows('reopen', $publishedEvaluation))->toBeTrue();
    expect(Gate::forUser($user)->allows('reopen', $draftEvaluation))->toBeFalse();
});
