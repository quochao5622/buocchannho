<?php

use Carbon\Carbon;
use DamodarBhattarai\Settings\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Employee\Models\AttendanceCorrectionRequest;
use Quochao56\Employee\Models\Employee;
use Quochao56\Employee\Models\EmployeeAttendance;
use Quochao56\Employee\Services\AttendanceCheckinService;
use Quochao56\Scheduler\Models\Schedule;
use Quochao56\Student\Models\Student;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach ([
        'office_morning_start' => '08:00:00',
        'office_morning_end' => '12:00:00',
        'office_afternoon_start' => '13:30:00',
        'office_afternoon_end' => '17:30:00',
        'office_evening_start' => '18:00:00',
        'office_evening_end' => '21:00:00',
        'office_allowed_late_minutes' => 15,
        'allow_flagged_location' => true,
    ] as $key => $value) {
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => json_encode($value),
                'label' => $key,
                'type' => is_int($value) ? 'text' : 'time',
                'group' => 'attendance',
                'tab_order' => 1,
            ]
        );
    }

    cache()->forget('damodarbhattarai-settings');

    $this->employee = Employee::create([
        'employee_code' => 'GV-ATT-001',
        'name' => 'Attendance Teacher',
        'email' => 'attendance.teacher@example.com',
        'status' => 'active',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
    cache()->forget('damodarbhattarai-settings');
});

it('resolves session correctly when attendance settings include seconds', function () {
    expect(EmployeeAttendance::resolveSessionForDateTime(Carbon::parse('2026-07-07 08:30:00')))
        ->toBe(EmployeeAttendance::SESSION_MORNING);

    expect(EmployeeAttendance::resolveSessionForDateTime(Carbon::parse('2026-07-07 15:30:00')))
        ->toBe(EmployeeAttendance::SESSION_AFTERNOON);

    expect(EmployeeAttendance::resolveSessionForDateTime(Carbon::parse('2026-07-07 19:15:00')))
        ->toBe(EmployeeAttendance::SESSION_EVENING);
});

it('keeps a single record when checking in multiple times in the same session', function () {
    $service = app(AttendanceCheckinService::class);

    Carbon::setTestNow(Carbon::parse('2026-07-07 08:05:00'));
    $morningAttendance1 = $service->checkIn($this->employee, []);

    Carbon::setTestNow(Carbon::parse('2026-07-07 08:10:00'));
    $morningAttendance2 = $service->checkIn($this->employee, []);

    expect($morningAttendance2->id)->toEqual($morningAttendance1->id);

    $records = EmployeeAttendance::query()
        ->where('employee_id', $this->employee->id)
        ->whereDate('date', '2026-07-07')
        ->get();

    expect($records)->toHaveCount(1);
});

it('auto closes an open morning attendance when checking in again in the evening', function () {
    $service = app(AttendanceCheckinService::class);

    Carbon::setTestNow(Carbon::parse('2026-07-07 07:45:00'));
    $morningAttendance = $service->checkIn($this->employee, [
        'ip' => '127.0.0.1',
    ]);

    expect($morningAttendance->session)->toEqual(EmployeeAttendance::SESSION_MORNING);
    expect($morningAttendance->check_out_at)->toBeNull();

    Carbon::setTestNow(Carbon::parse('2026-07-07 18:10:00'));
    $eveningAttendance = $service->checkIn($this->employee, [
        'ip' => '127.0.0.1',
    ]);

    expect($eveningAttendance->session)->toEqual(EmployeeAttendance::SESSION_EVENING);
    expect($eveningAttendance->check_out_at)->toBeNull();

    $morningAttendance->refresh();

    expect($morningAttendance->check_out_at?->format('H:i'))->toBe('12:00');
    expect($morningAttendance->auto_closed)->toBeTrue();
    expect($morningAttendance->flagged_missing_checkout)->toBeTrue();
    expect((float) $morningAttendance->total_hours)->toBe(4.0);

    $records = EmployeeAttendance::query()
        ->where('employee_id', $this->employee->id)
        ->whereDate('date', '2026-07-07')
        ->orderBy('session')
        ->get();

    expect($records)->toHaveCount(2);
    expect($records->pluck('session')->all())->toBe([
        EmployeeAttendance::SESSION_EVENING,
        EmployeeAttendance::SESSION_MORNING,
    ]);
});

it('creates separate attendance records for morning and evening sessions on the same date when checked in separately', function () {
    $service = app(AttendanceCheckinService::class);

    // Morning ca
    Carbon::setTestNow(Carbon::parse('2026-07-07 08:05:00'));
    $morningAttendance = $service->checkIn($this->employee, []);
    Carbon::setTestNow(Carbon::parse('2026-07-07 11:45:00'));
    $service->checkOut($morningAttendance->fresh(), []);

    // Evening ca (no afternoon)
    Carbon::setTestNow(Carbon::parse('2026-07-07 18:10:00'));
    $eveningAttendance = $service->checkIn($this->employee, []);
    Carbon::setTestNow(Carbon::parse('2026-07-07 20:40:00'));
    $service->checkOut($eveningAttendance->fresh(), []);

    $records = EmployeeAttendance::query()
        ->where('employee_id', $this->employee->id)
        ->whereDate('date', '2026-07-07')
        ->orderBy('check_in_at', 'asc')
        ->get();

    expect($records)->toHaveCount(2);
    expect($records[0]->session)->toEqual(EmployeeAttendance::SESSION_MORNING);
    expect($records[1]->session)->toEqual(EmployeeAttendance::SESSION_EVENING);
});

it('calculates total hours correctly for various check-in and check-out cases', function () {
    $service = app(AttendanceCheckinService::class);

    // Create a student for scheduling
    $student = Student::create([
        'student_code' => 'HS001',
        'name' => 'Nguyen Van A',
        'status' => 'active',
    ]);

    // Create evening schedules for this employee on Tuesday 2026-07-07
    // Day of week for Tuesday is 3 (1=CN, 2=T2, 3=T3)
    $scheduleA = Schedule::create([
        'student_id' => $student->id,
        'employee_id' => $this->employee->id,
        'title' => 'Evening Class 1',
        'type' => 'individual',
        'day_of_week' => [3],
        'start_time' => '18:00:00',
        'end_time' => '19:30:00', // 1.5 hours
        'start_date' => '2026-07-01',
        'status' => 'active',
    ]);

    $scheduleB = Schedule::create([
        'student_id' => $student->id,
        'employee_id' => $this->employee->id,
        'title' => 'Evening Class 2',
        'type' => 'individual',
        'day_of_week' => [3],
        'start_time' => '19:30:00',
        'end_time' => '20:30:00', // 1.0 hour
        'start_date' => '2026-07-01',
        'status' => 'active',
    ]);

    // Case 1: Check-in: 07:30, Check-out: 17:45 (Spans morning and afternoon -> splits)
    // Morning (12:00 - 07:30 = 4.5h) and Afternoon (17:45 - 13:30 = 4.25h)
    Carbon::setTestNow(Carbon::parse('2026-07-07 07:30:00'));
    $attendance1 = $service->checkIn($this->employee, []);
    Carbon::setTestNow(Carbon::parse('2026-07-07 17:45:00'));
    $attendance1 = $service->checkOut($attendance1->fresh(), []);

    // Returned attendance is the final (afternoon) one
    expect($attendance1->session)->toEqual(EmployeeAttendance::SESSION_AFTERNOON);
    expect((float) $attendance1->total_hours)->toEqual(4.25);

    // Verify morning record exists and is closed correctly
    $morningRecord = EmployeeAttendance::where('employee_id', $this->employee->id)
        ->whereDate('date', '2026-07-07')
        ->where('session', EmployeeAttendance::SESSION_MORNING)
        ->first();
    expect($morningRecord)->not->toBeNull();
    expect((float) $morningRecord->total_hours)->toEqual(4.5);

    // Reset database for other clean cases
    EmployeeAttendance::truncate();

    // Case 2: Check-in: 07:30, Check-out: 12:00 (Same session -> no split)
    // 07:30 to 12:00 = 4.5h
    Carbon::setTestNow(Carbon::parse('2026-07-07 07:30:00'));
    $attendance2 = $service->checkIn($this->employee, []);
    Carbon::setTestNow(Carbon::parse('2026-07-07 12:00:00'));
    $attendance2 = $service->checkOut($attendance2->fresh(), []);
    expect((float) $attendance2->total_hours)->toEqual(4.5);

    EmployeeAttendance::truncate();

    // Case 3: Check-in: 07:30, Check-out: 20:00 (Spans morning, afternoon, evening -> splits into 3)
    // Morning (4.5h) + Afternoon (4.0h) + Evening (1.5h + 1.0h = 2.5h)
    Carbon::setTestNow(Carbon::parse('2026-07-07 07:30:00'));
    $attendance3 = $service->checkIn($this->employee, []);
    Carbon::setTestNow(Carbon::parse('2026-07-07 20:00:00'));
    $attendance3 = $service->checkOut($attendance3->fresh(), []);

    expect($attendance3->session)->toEqual(EmployeeAttendance::SESSION_EVENING);
    expect((float) $attendance3->total_hours)->toEqual(2.5); // Evening hours only on evening record

    $records3 = EmployeeAttendance::where('employee_id', $this->employee->id)->whereDate('date', '2026-07-07')->get();
    expect($records3)->toHaveCount(3);

    EmployeeAttendance::truncate();

    // Case 4: Check-in: 13:00, Check-out: 18:00 (Spans afternoon and evening -> splits into 2)
    // Afternoon (13:00 to 17:30 = 4.5h) + Evening (1.5h + 1.0h = 2.5h)
    Carbon::setTestNow(Carbon::parse('2026-07-07 13:00:00'));
    $attendance4 = $service->checkIn($this->employee, []);
    Carbon::setTestNow(Carbon::parse('2026-07-07 18:00:00'));
    $attendance4 = $service->checkOut($attendance4->fresh(), []);

    expect($attendance4->session)->toEqual(EmployeeAttendance::SESSION_EVENING);
    expect((float) $attendance4->total_hours)->toEqual(2.5);

    $records4 = EmployeeAttendance::where('employee_id', $this->employee->id)->whereDate('date', '2026-07-07')->get();
    expect($records4)->toHaveCount(2);

    EmployeeAttendance::truncate();

    // Case 5: Check-in: 18:00, Check-out: 20:30 (Same session -> no split)
    // Evening teaching hours: 1.5h + 1.0h = 2.5h
    Carbon::setTestNow(Carbon::parse('2026-07-07 18:00:00'));
    $attendance5 = $service->checkIn($this->employee, []);
    Carbon::setTestNow(Carbon::parse('2026-07-07 20:30:00'));
    $attendance5 = $service->checkOut($attendance5->fresh(), []);
    expect((float) $attendance5->total_hours)->toEqual(2.5);

    EmployeeAttendance::truncate();

    // Case 6: Check-in: 07:30, Check-out: 15:00 (Spans morning and afternoon -> splits into 2)
    // Morning (4.5h) + Afternoon (13:30 to 15:00 = 1.5h)
    Carbon::setTestNow(Carbon::parse('2026-07-07 07:30:00'));
    $attendance6 = $service->checkIn($this->employee, []);
    Carbon::setTestNow(Carbon::parse('2026-07-07 15:00:00'));
    $attendance6 = $service->checkOut($attendance6->fresh(), []);

    expect($attendance6->session)->toEqual(EmployeeAttendance::SESSION_AFTERNOON);
    expect((float) $attendance6->total_hours)->toEqual(1.5);

    $records6 = EmployeeAttendance::where('employee_id', $this->employee->id)->whereDate('date', '2026-07-07')->get();
    expect($records6)->toHaveCount(2);

    EmployeeAttendance::truncate();

    // Case 7: Check-in: 07:30, Check-out: 18:30 (Spans morning, afternoon, evening but NO classes -> splits into 3 with evening having 0h)
    Schedule::truncate();
    Carbon::setTestNow(Carbon::parse('2026-07-07 07:30:00'));
    $attendance7 = $service->checkIn($this->employee, []);
    Carbon::setTestNow(Carbon::parse('2026-07-07 18:30:00'));
    $attendance7 = $service->checkOut($attendance7->fresh(), []);

    expect($attendance7->session)->toEqual(EmployeeAttendance::SESSION_EVENING);
    expect((float) $attendance7->total_hours)->toEqual(0.0);

    $records7 = EmployeeAttendance::where('employee_id', $this->employee->id)->whereDate('date', '2026-07-07')->get();
    expect($records7)->toHaveCount(3);
});

it('calculates session and total hours correctly when approving an attendance correction request', function () {
    $request = AttendanceCorrectionRequest::create([
        'employee_id' => $this->employee->id,
        'attendance_date' => '2026-07-07',
        'requested_check_in_at' => '2026-07-07 07:30:00',
        'requested_check_out_at' => '2026-07-07 16:45:00',
        'reason' => 'Forgot to check-in/out',
        'status' => 'pending',
    ]);

    // Simulate approving the request using the table action logic
    $requestedCheckIn = $request->requested_check_in_at ? Carbon::parse($request->requested_check_in_at) : null;
    $requestedCheckOut = $request->requested_check_out_at ? Carbon::parse($request->requested_check_out_at) : null;

    if ($requestedCheckIn && $requestedCheckOut) {
        $checkInSession = EmployeeAttendance::resolveSessionForDateTime($requestedCheckIn);
        $checkoutSession = EmployeeAttendance::resolveSessionForDateTime($requestedCheckOut);
        $dateStr = Carbon::parse($request->attendance_date)->toDateString();

        if ($checkInSession === EmployeeAttendance::SESSION_MORNING && $checkoutSession === EmployeeAttendance::SESSION_AFTERNOON) {
            // Sáng -> Chiều
            $morningEndStr = settings('office_morning_end', '12:00');
            $morningEnd = Carbon::parse($dateStr.' '.$morningEndStr);
            $morningHours = max(0.0, round(abs($morningEnd->diffInMinutes($requestedCheckIn)) / 60, 2));

            EmployeeAttendance::updateOrCreate(
                ['employee_id' => $request->employee_id, 'date' => $request->attendance_date, 'session' => EmployeeAttendance::SESSION_MORNING],
                [
                    'check_in_at' => $requestedCheckIn,
                    'check_out_at' => $morningEnd,
                    'total_hours' => $morningHours,
                    'corrected_by' => auth()->id(),
                    'corrected_at' => now(),
                    'status' => 'present',
                    'auto_closed' => true,
                    'notes' => 'Hệ thống tự động checkout cuối ca sáng (duyệt sửa công)',
                ]
            );

            $afternoonStartStr = settings('office_afternoon_start', '13:30');
            $afternoonStart = Carbon::parse($dateStr.' '.$afternoonStartStr);
            $afternoonHours = max(0.0, round(abs($requestedCheckOut->diffInMinutes($afternoonStart)) / 60, 2));

            EmployeeAttendance::updateOrCreate(
                ['employee_id' => $request->employee_id, 'date' => $request->attendance_date, 'session' => EmployeeAttendance::SESSION_AFTERNOON],
                [
                    'check_in_at' => $afternoonStart,
                    'check_out_at' => $requestedCheckOut,
                    'total_hours' => $afternoonHours,
                    'corrected_by' => auth()->id(),
                    'corrected_at' => now(),
                    'status' => 'present',
                    'notes' => 'Hệ thống tự động check-in ca chiều (duyệt sửa công)',
                ]
            );
        }
    }

    $records = EmployeeAttendance::where('employee_id', $request->employee_id)->whereDate('date', $request->attendance_date)->get();
    expect($records)->toHaveCount(2);

    $morning = $records->firstWhere('session', EmployeeAttendance::SESSION_MORNING);
    $afternoon = $records->firstWhere('session', EmployeeAttendance::SESSION_AFTERNOON);

    expect((float) $morning->total_hours)->toEqual(4.5);
    expect((float) $afternoon->total_hours)->toEqual(3.25);
});
