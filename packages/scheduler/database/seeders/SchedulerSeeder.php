<?php

namespace Quochao56\Scheduler\Database\Seeders;

use Illuminate\Database\Seeder;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Models\Attendance;
use Quochao56\Scheduler\Models\Classroom;
use Quochao56\Scheduler\Models\Schedule;
use Quochao56\Scheduler\Models\ScheduleException;
use Quochao56\Student\Models\Student;

class SchedulerSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $employees = Employee::all();
        $classrooms = Classroom::all();

        if ($students->isEmpty() || $employees->isEmpty()) {
            return;
        }

        $student1 = $students->first();
        $student2 = $students->skip(1)->first() ?? $student1;
        $student3 = $students->skip(2)->first() ?? $student1;

        $employee1 = $employees->first();
        $employee2 = $employees->skip(1)->first() ?? $employee1;
        $employee3 = $employees->skip(2)->first() ?? $employee1;

        $room = $classrooms->first();

        // 1. Seed schedules
        $schedule1 = Schedule::create([
            'student_id' => $student1->id,
            'employee_id' => $employee1->id,
            'classroom_id' => $room?->id,
            'title' => 'Can thiệp Ngôn ngữ 1-1',
            'type' => 'individual',
            'day_of_week' => [2], // Thứ hai
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
            'start_date' => now()->startOfYear(),
            'end_date' => null,
            'status' => 'active',
            'notes' => 'Tập trung luyện phát âm âm đầu và từ đôi.',
        ]);

        $schedule2 = Schedule::create([
            'student_id' => $student2->id,
            'employee_id' => $employee2->id,
            'classroom_id' => $room?->id,
            'title' => 'Hoạt động Trị liệu OT 1-1',
            'type' => 'individual',
            'day_of_week' => [3], // Thứ ba
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'start_date' => now()->startOfYear(),
            'end_date' => null,
            'status' => 'active',
            'notes' => 'Tập trung điều hợp vận động thô và giữ thăng bằng.',
        ]);

        $schedule3 = Schedule::create([
            'student_id' => $student3->id,
            'employee_id' => $employee1->id,
            'classroom_id' => $room?->id,
            'title' => 'Lớp kỹ năng giao tiếp nhóm',
            'type' => 'group',
            'day_of_week' => [4], // Thứ tư
            'start_time' => '14:00:00',
            'end_time' => '15:30:00',
            'start_date' => now()->startOfYear(),
            'end_date' => null,
            'status' => 'active',
            'notes' => 'Hoạt động tương tác nhóm và trò chơi luân phiên.',
        ]);

        // 2. Seed exceptions
        // Exception 1: Cancel schedule 1 on Monday next week
        $nextMonday = now()->next(2)->format('Y-m-d'); // Monday next week
        ScheduleException::create([
            'schedule_id' => $schedule1->id,
            'exception_date' => $nextMonday,
            'action' => 'cancel',
            'reason' => 'Trẻ bị sốt cao phải đi bệnh viện khám',
            'notes' => 'Phụ huynh báo nghỉ lúc 7:00 sáng.',
        ]);

        // Exception 2: Substitute teacher for schedule 2 on Tuesday next week
        $nextTuesday = now()->next(3)->format('Y-m-d'); // Tuesday next week
        ScheduleException::create([
            'schedule_id' => $schedule2->id,
            'exception_date' => $nextTuesday,
            'action' => 'substitute',
            'new_employee_id' => $employee3->id,
            'reason' => 'Giáo viên phụ trách nghỉ phép kết hôn',
            'notes' => 'Đã bàn giao giáo án buổi can thiệp.',
        ]);

        // 3. Seed attendances
        // Present attendance last week
        $lastMonday = now()->previous(2)->format('Y-m-d');
        Attendance::create([
            'student_id' => $student1->id,
            'schedule_id' => $schedule1->id,
            'attendance_date' => $lastMonday,
            'status' => 'present',
            'check_in_at' => $lastMonday.' 07:58:00',
            'check_out_at' => $lastMonday.' 09:30:00',
            'total_hours' => 1.5,
            'verified_by_employee_id' => $employee1->id,
            'actual_employee_id' => $employee1->id,
            'notes' => 'Trẻ đi học đúng giờ, hợp tác tốt.',
        ]);

        $lastTuesday = now()->previous(3)->format('Y-m-d');
        Attendance::create([
            'student_id' => $student2->id,
            'schedule_id' => $schedule2->id,
            'attendance_date' => $lastTuesday,
            'status' => 'absent_excused',
            'verified_by_employee_id' => $employee2->id,
            'actual_employee_id' => $employee2->id,
            'notes' => 'Phụ huynh xin nghỉ phép đi du lịch gia đình.',
        ]);
    }
}
