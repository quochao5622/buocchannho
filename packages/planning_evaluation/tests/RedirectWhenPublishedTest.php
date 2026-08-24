<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Core\Models\User;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\PlanningEvaluation\Tests\TestCase;
use Quochao56\Student\Models\Student;
use Spatie\Permission\Models\Permission;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->student = Student::create([
        'student_code' => 'HS_REDIRECT',
        'name' => 'Student Redirect',
    ]);

    $this->employee = Employee::create([
        'employee_code' => 'GV_REDIRECT',
        'name' => 'Teacher Redirect',
    ]);

    Permission::findOrCreate('plannings.index', 'web');
    Permission::findOrCreate('plannings.show', 'web');
    Permission::findOrCreate('plannings.edit', 'web');
    Permission::findOrCreate('plannings.approve', 'web');
    Permission::findOrCreate('plannings.view_all', 'web');

    Permission::findOrCreate('evaluations.index', 'web');
    Permission::findOrCreate('evaluations.show', 'web');
    Permission::findOrCreate('evaluations.edit', 'web');
    Permission::findOrCreate('evaluations.approve', 'web');
    Permission::findOrCreate('evaluations.view_all', 'web');

    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('redirects from edit planning to view planning when status is published', function () {
    $user = clone User::factory()->create(['is_super_admin' => false, 'is_active' => true]);
    $user->givePermissionTo(['plannings.index', 'plannings.show', 'plannings.edit', 'plannings.view_all']);

    $planning = Planning::create([
        'name' => 'Published Planning For Redirect',
        'student_id' => $this->student->id,
        'employee_id' => $this->employee->id,
        'status' => BaseStatusEnum::Published,
    ]);

    $this->actingAs($user);

    $response = $this->get(PlanningResource::getUrl('edit', ['record' => $planning]));

    $response->assertRedirect(PlanningResource::getUrl('view', ['record' => $planning]));
});

it('redirects from edit evaluation to view evaluation when status is published', function () {
    $user = clone User::factory()->create(['is_super_admin' => false, 'is_active' => true]);
    $user->givePermissionTo(['evaluations.index', 'evaluations.show', 'evaluations.edit', 'evaluations.view_all', 'plannings.index', 'plannings.show', 'plannings.view_all']);

    $planning = Planning::create([
        'name' => 'Planning for Evaluation Redirect',
        'student_id' => $this->student->id,
        'employee_id' => $this->employee->id,
        'status' => BaseStatusEnum::Published,
    ]);

    $evaluation = Evaluation::create([
        'name' => 'Published Evaluation For Redirect',
        'planning_id' => $planning->id,
        'status' => BaseStatusEnum::Published,
    ]);

    $this->actingAs($user);

    $response = $this->get(EvaluationResource::getUrl('edit', [
        'planning' => $planning->id,
        'record' => $evaluation->id,
    ]));

    $response->assertRedirect(EvaluationResource::getUrl('view', [
        'planning' => $planning->id,
        'record' => $evaluation->id,
    ]));
});
