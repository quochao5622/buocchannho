<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Employee\Models\Employee;
use Quochao56\SessionLog\Filament\Enums\BehaviorIntensityEnum;
use Quochao56\SessionLog\Models\BehaviorIncident;
use Quochao56\SessionLog\Tests\TestCase;
use Quochao56\Student\Models\Student;

uses(TestCase::class, RefreshDatabase::class);

it('can create a behavior incident record', function () {
    $student = Student::create([
        'student_code' => 'HS_BI1',
        'name' => 'Lê Văn C',
        'status' => 'active',
    ]);

    $employee = Employee::create([
        'employee_code' => 'GV_BI1',
        'name' => 'Nguyễn Thị D',
    ]);

    $incident = BehaviorIncident::create([
        'student_id' => $student->id,
        'employee_id' => $employee->id,
        'incident_date' => now(),
        'antecedent' => 'Yêu cầu trẻ làm bài tập nhóm',
        'behavior' => 'Trẻ la khóc lớn tiếng và ném bút màu',
        'consequence' => 'Cho trẻ ra khu vực yên tĩnh 5 phút',
        'duration_minutes' => 10,
        'intensity' => BehaviorIntensityEnum::Moderate->value,
        'notes' => 'Cần quan sát thêm hành vi tương tự',
    ]);

    expect($incident->student_id)->toBe($student->id);
    expect($incident->intensity)->toBe(BehaviorIntensityEnum::Moderate);
    expect($incident->duration_minutes)->toBe(10);
});
