<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Acl\Filament\Resources\RoleResource;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Models\Attendance;
use Quochao56\Scheduler\Models\Schedule;
use Quochao56\Scheduler\Models\ScheduleException;
use Quochao56\Scheduler\Rules\NoConflictScheduleRule;
use Quochao56\Student\Models\Student;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Sync permissions from config
    RoleResource::syncPermissionsToDatabase();

    // Create dummy students and employees
    $this->studentA = Student::create([
        'student_code' => 'HS001',
        'name' => 'Nguyen Van A',
        'status' => 'active',
    ]);

    $this->studentB = Student::create([
        'student_code' => 'HS002',
        'name' => 'Nguyen Van B',
        'status' => 'active',
    ]);

    $this->employeeA = Employee::create([
        'employee_code' => 'GV001',
        'name' => 'Teacher A',
        'email' => 'teacherA@example.com',
        'status' => 'active',
    ]);

    $this->employeeB = Employee::create([
        'employee_code' => 'GV002',
        'name' => 'Teacher B',
        'email' => 'teacherB@example.com',
        'status' => 'active',
    ]);
});

it('can create a schedule successfully', function () {
    $schedule = Schedule::create([
        'student_id' => $this->studentA->id,
        'employee_id' => $this->employeeA->id,
        'title' => 'Can thiệp Ngôn ngữ 1-1',
        'type' => 'individual',
        'day_of_week' => 2, // Thứ hai
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'start_date' => '2026-07-01',
        'status' => 'active',
    ]);

    expect($schedule)->toBeInstanceOf(Schedule::class);
    $this->assertDatabaseHas('schedules', [
        'id' => $schedule->id,
        'title' => 'Can thiệp Ngôn ngữ 1-1',
    ]);
});

it('prevents overlapping schedules for the same teacher', function () {
    // Create first schedule for teacher A on Monday 8:00 - 9:30
    Schedule::create([
        'student_id' => $this->studentA->id,
        'employee_id' => $this->employeeA->id,
        'title' => 'Schedule 1',
        'type' => 'individual',
        'day_of_week' => 2,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'start_date' => '2026-07-01',
        'status' => 'active',
    ]);

    // Validate conflict using the rule for teacher A on Monday 9:00 - 10:30 (overlaps 9:00 - 9:30)
    $rule = new NoConflictScheduleRule(
        studentId: $this->studentB->id,
        employeeId: $this->employeeA->id,
        dayOfWeek: 2,
        startTime: '09:00:00',
        endTime: '10:30:00',
        startDate: '2026-07-01'
    );

    $hasFailed = false;
    $rule->validate('employee_id', $this->employeeA->id, function ($message) use (&$hasFailed) {
        $hasFailed = true;
        expect($message)->toContain('Giáo viên này đã có lịch dạy khác trùng khung giờ');
    });

    expect($hasFailed)->toBeTrue();
});

it('releases teacher availability when cancel exception is registered', function () {
    // Create schedule for teacher A on Monday 8:00 - 9:30
    $schedule = Schedule::create([
        'student_id' => $this->studentA->id,
        'employee_id' => $this->employeeA->id,
        'title' => 'Schedule 1',
        'type' => 'individual',
        'day_of_week' => 2,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'start_date' => '2026-07-01',
        'status' => 'active',
    ]);

    // Teacher A is busy on 2026-07-06 (Monday) at 8:00 - 9:30
    $availableBefore = NoConflictScheduleRule::isTeacherAvailableOnDate(
        $this->employeeA->id,
        '2026-07-06',
        '08:00:00',
        '09:30:00'
    );
    expect($availableBefore)->toBeFalse();

    // Register cancel exception on 2026-07-06
    ScheduleException::create([
        'schedule_id' => $schedule->id,
        'exception_date' => '2026-07-06',
        'action' => 'cancel',
        'reason' => 'Học sinh vắng',
    ]);

    // Teacher A should now be available on 2026-07-06 at 8:00 - 9:30
    $availableAfter = NoConflictScheduleRule::isTeacherAvailableOnDate(
        $this->employeeA->id,
        '2026-07-06',
        '08:00:00',
        '09:30:00'
    );
    expect($availableAfter)->toBeTrue();
});

it('tracks actual teacher on attendance when substitute exception is registered', function () {
    // Create schedule for teacher A
    $schedule = Schedule::create([
        'student_id' => $this->studentA->id,
        'employee_id' => $this->employeeA->id,
        'title' => 'Schedule 1',
        'type' => 'individual',
        'day_of_week' => 2,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'start_date' => '2026-07-01',
        'status' => 'active',
    ]);

    // Register substitute exception for teacher B on 2026-07-06
    ScheduleException::create([
        'schedule_id' => $schedule->id,
        'exception_date' => '2026-07-06',
        'action' => 'substitute',
        'new_employee_id' => $this->employeeB->id,
        'reason' => 'Teacher A is sick',
    ]);

    // Register attendance log for 2026-07-06
    $attendance = Attendance::create([
        'student_id' => $this->studentA->id,
        'schedule_id' => $schedule->id,
        'attendance_date' => '2026-07-06',
        'status' => 'present',
        'verified_by_employee_id' => $this->employeeB->id,
        'actual_employee_id' => $this->employeeB->id,
    ]);

    $this->assertDatabaseHas('attendances', [
        'id' => $attendance->id,
        'actual_employee_id' => $this->employeeB->id,
    ]);
});

it('can quick create schedules for 1 month with multiple students/teachers and skips conflicts', function () {
    // Create a conflicting schedule first for Teacher A on Monday (2) 08:00 - 09:30
    Schedule::create([
        'student_id' => $this->studentA->id,
        'employee_id' => $this->employeeA->id,
        'title' => 'Conflict Schedule',
        'type' => 'individual',
        'day_of_week' => 2,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'start_date' => '2026-07-01',
        'status' => 'active',
    ]);

    // Parameters
    $type = 'group';
    $title = 'Lớp kỹ năng nhóm';
    $dayOfWeek = 2; // Monday
    $startTime = '08:00:00';
    $endTime = '09:30:00';
    $startDate = '2026-07-01';
    $endDate = '2026-08-01';
    $equipmentId = null;
    $employeeIds = [$this->employeeA->id, $this->employeeB->id];
    $studentIds = [$this->studentA->id, $this->studentB->id];

    $successCount = 0;
    $conflicts = [];

    foreach ($employeeIds as $employeeId) {
        $teacherConflict = Schedule::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->first();

        if ($teacherConflict) {
            $conflicts[] = 'Teacher conflict: '.$employeeId;

            continue;
        }

        foreach ($studentIds as $studentId) {
            $studentConflict = Schedule::where('student_id', $studentId)
                ->where('status', 'active')
                ->where('day_of_week', $dayOfWeek)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->first();

            if ($studentConflict) {
                $conflicts[] = 'Student conflict: '.$studentId;

                continue;
            }

            Schedule::create([
                'student_id' => $studentId,
                'employee_id' => $employeeId,
                'equipment_id' => $equipmentId,
                'title' => $title,
                'type' => $type,
                'day_of_week' => $dayOfWeek,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
            ]);
            $successCount++;
        }
    }

    expect($successCount)->toBe(1);
    expect($conflicts)->toContain('Teacher conflict: '.$this->employeeA->id);
    expect($conflicts)->toContain('Student conflict: '.$this->studentA->id);

    $this->assertDatabaseHas('schedules', [
        'employee_id' => $this->employeeB->id,
        'student_id' => $this->studentB->id,
        'title' => 'Lớp kỹ năng nhóm',
        'end_date' => '2026-08-01 00:00:00',
    ]);
});
