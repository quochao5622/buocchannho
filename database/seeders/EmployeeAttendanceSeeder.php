<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Quochao56\Core\Models\User;
use Quochao56\Employee\Models\AttendanceCorrectionRequest;
use Quochao56\Employee\Models\Employee;
use Quochao56\Employee\Models\EmployeeAttendance;
use Quochao56\Employee\Models\LeaveRequest;
use Quochao56\Student\Models\Student;
use Quochao56\Student\Models\StudentLeaveRequest;

class EmployeeAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        $adminUser = User::where('email', 'test@example.com')->first();

        foreach ($employees as $employee) {
            // Seed an approved Leave Request
            $leave = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type' => 'annual',
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->subDays(5),
                'half_day' => false,
                'reason' => 'Đi khám bệnh định kỳ',
                'status' => 'approved',
                'approved_by' => $adminUser?->id,
                'approved_at' => Carbon::now()->subDays(6),
            ]);

            // Create leave attendance record
            EmployeeAttendance::create([
                'employee_id' => $employee->id,
                'date' => Carbon::now()->subDays(5)->toDateString(),
                'status' => 'on_leave',
                'notes' => 'Nghỉ phép: '.$leave->reason,
            ]);

            // Seed normal attendances for other days
            for ($i = 1; $i <= 4; $i++) {
                $date = Carbon::now()->subDays($i);
                if ($date->dayOfWeek === Carbon::SUNDAY) {
                    continue;
                }

                // Normal check-in/out
                EmployeeAttendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                    'check_in_at' => (clone $date)->setTime(8, 0, 0),
                    'check_out_at' => (clone $date)->setTime(17, 0, 0),
                    'status' => 'present',
                    'total_hours' => 8.0,
                    'check_in_latitude' => 10.776889,
                    'check_in_longitude' => 106.700806,
                    'check_out_latitude' => 10.776889,
                    'check_out_longitude' => 106.700806,
                ]);
            }

            // Seed a pending correction request
            AttendanceCorrectionRequest::create([
                'employee_id' => $employee->id,
                'attendance_date' => Carbon::now()->subDays(1)->toDateString(),
                'requested_check_in_at' => Carbon::now()->subDays(1)->setTime(7, 55, 0),
                'requested_check_out_at' => Carbon::now()->subDays(1)->setTime(17, 5, 0),
                'reason' => 'Quên bấm chấm công trên điện thoại',
                'status' => 'pending',
            ]);
        }

        $students = Student::all();
        foreach ($students as $student) {
            StudentLeaveRequest::create([
                'student_id' => $student->id,
                'start_date' => Carbon::now()->addDays(2),
                'end_date' => Carbon::now()->addDays(2),
                'reason' => 'Gia đình có việc bận về quê',
                'status' => 'pending',
                'created_by' => $adminUser?->id,
            ]);

            StudentLeaveRequest::create([
                'student_id' => $student->id,
                'start_date' => Carbon::now()->subDays(2),
                'end_date' => Carbon::now()->subDays(2),
                'reason' => 'Bé bị ốm sốt',
                'status' => 'approved',
                'approved_by' => $adminUser?->id,
                'approved_at' => Carbon::now()->subDays(3),
                'created_by' => $adminUser?->id,
            ]);
        }
    }
}
