<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Employee\Models\Employee;
use Quochao56\SessionLog\Filament\Enums\DailyLogEmotionEnum;
use Quochao56\SessionLog\Filament\Enums\DailyLogRatingEnum;
use Quochao56\SessionLog\Filament\Enums\DailyLogStatusEnum;
use Quochao56\SessionLog\Models\DailyLog;
use Quochao56\SessionLog\Tests\TestCase;
use Quochao56\Student\Models\Student;

uses(TestCase::class, RefreshDatabase::class);

it('can create a daily log record', function () {
    $student = Student::create([
        'student_code' => 'HS_DL1',
        'name' => 'Nguyễn Văn A',
        'status' => 'active',
    ]);

    $employee = Employee::create([
        'employee_code' => 'GV_DL1',
        'name' => 'Trần Thị B',
    ]);

    $dailyLog = DailyLog::create([
        'student_id' => $student->id,
        'employee_id' => $employee->id,
        'log_date' => now()->toDateString(),
        'emotion' => DailyLogEmotionEnum::Happy->value,
        'focus_level' => DailyLogRatingEnum::Good->value,
        'cooperation_level' => DailyLogRatingEnum::Good->value,
        'eating_note' => 'Ăn hết suất',
        'sleeping_note' => 'Ngủ ngoan',
        'status' => DailyLogStatusEnum::Completed->value,
    ]);

    expect($dailyLog->student_id)->toBe($student->id);
    expect($dailyLog->emotion)->toBe(DailyLogEmotionEnum::Happy);
    expect($dailyLog->status)->toBe(DailyLogStatusEnum::Completed);
});
