<?php

use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\PlanningEvaluation\Tests\TestCase;
use Quochao56\Student\Models\Student;

uses(TestCase::class);

it('clones a planning successfully and forces status to draft', function () {
    $student = new Student;
    $student->name = 'Original Student';
    $student->save();

    $newStudent = new Student;
    $newStudent->name = 'Cloned Student';
    $newStudent->save();

    $employee = new Employee;
    $employee->name = 'Teacher';
    $employee->save();

    $planning = new Planning;
    $planning->name = 'Original Planning';
    $planning->student_id = $student->id;
    $planning->employee_id = $employee->id;
    $planning->status = BaseStatusEnum::Published;
    $planning->planning_details = [
        [
            'linh_vuc' => [['content' => 'Vận động']],
            'muc_tieu' => [['content' => 'Chạy bộ']],
            'hoat_dong' => [],
            'phuong_tien' => [],
            'muc_tieu_du_phong' => [],
        ],
    ];
    $planning->save();

    // Perform clone/replicate logic
    $cloned = $planning->replicate();
    $cloned->student_id = $newStudent->id;
    $cloned->name = $planning->name.' (Nhân bản)';
    $cloned->status = BaseStatusEnum::Draft;
    $cloned->save();

    expect($cloned->id)->not->toBe($planning->id);
    expect($cloned->student_id)->toBe($newStudent->id);
    expect($cloned->status)->toBe(BaseStatusEnum::Draft);
    expect($cloned->name)->toBe('Original Planning (Nhân bản)');
    expect($cloned->planning_details[0]['muc_tieu'][0]['content'])->toBe('Chạy bộ');
});
