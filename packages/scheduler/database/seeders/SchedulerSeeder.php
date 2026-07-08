<?php

namespace Quochao56\Scheduler\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Quochao56\Employee\Models\Employee;
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

        $room = $classrooms->first();

        // Xóa dữ liệu cũ để tạo seeder sạch sẽ, không bị trùng lặp
        ScheduleException::query()->delete();
        Schedule::query()->delete();

        // 1. Tạo lịch dạy cố định cho các giáo viên
        $schedules = [];
        $scheduleTitles = [
            'Can thiệp Ngôn ngữ 1-1',
            'Hoạt động Trị liệu OT 1-1',
            'Lớp kỹ năng giao tiếp nhóm',
            'Can thiệp Hành vi 1-1',
            'Phát triển Nhận thức 1-1',
        ];

        // 15 lịch dạy ngẫu nhiên cho các giáo viên và học sinh từ Thứ 2 đến Thứ 7
        for ($i = 0; $i < 15; $i++) {
            $student = $students->random();
            $employee = $employees->random();
            $title = $scheduleTitles[array_rand($scheduleTitles)];
            $dayOfWeek = ($i % 6) + 2; // 2 đến 7 (Thứ 2 đến Thứ 7)

            $timeSlots = [
                ['08:00:00', '09:30:00'],
                ['10:00:00', '11:30:00'],
                ['13:30:00', '15:00:00'],
                ['15:30:00', '17:00:00'],
                ['18:00:00', '19:30:00'],
                ['19:30:00', '21:00:00'],
            ];
            $slot = $timeSlots[$i % count($timeSlots)];

            $schedules[] = Schedule::create([
                'student_id' => $student->id,
                'employee_id' => $employee->id,
                'classroom_id' => $room?->id,
                'title' => $title.' ('.$student->name.')',
                'type' => $i % 4 === 0 ? 'group' : 'individual',
                'day_of_week' => [$dayOfWeek],
                'start_time' => $slot[0],
                'end_time' => $slot[1],
                'start_date' => Carbon::now()->subDays(60)->toDateString(),
                'end_date' => null,
                'status' => 'active',
                'notes' => 'Lịch can thiệp định kỳ hàng tuần.',
            ]);
        }

        // Đảm bảo tạo thêm lịch dạy ca tối cố định cho mọi giáo viên vào Thứ 2, 4, 6 để kiểm tra
        $eveningSlots = [
            ['18:00:00', '19:30:00'],
            ['19:30:00', '21:00:00'],
        ];
        foreach ($employees as $empIndex => $employee) {
            $student = $students->random();
            $title = $scheduleTitles[array_rand($scheduleTitles)];
            $slot = $eveningSlots[$empIndex % 2];

            $schedules[] = Schedule::create([
                'student_id' => $student->id,
                'employee_id' => $employee->id,
                'classroom_id' => $room?->id,
                'title' => $title.' (Ca tối - '.$student->name.')',
                'type' => 'individual',
                'day_of_week' => [2, 4, 6], // Thứ 2, 4, 6 (tương ứng Mon, Wed, Fri)
                'start_time' => $slot[0],
                'end_time' => $slot[1],
                'start_date' => Carbon::now()->subDays(60)->toDateString(),
                'end_date' => null,
                'status' => 'active',
                'notes' => 'Lịch can thiệp ca tối cố định hàng tuần.',
            ]);
        }

        // 2. Tạo lịch sử điểm danh của học sinh trong 30 ngày qua
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        foreach ($schedules as $index => $schedule) {
            for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
                $dayOfWeekNum = $date->dayOfWeek === 0 ? 1 : ($date->dayOfWeek + 1);

                if (! in_array($dayOfWeekNum, $schedule->day_of_week)) {
                    continue;
                }

                $dateStr = $date->toDateString();

                // Các case thực tế ngẫu nhiên (schedules hiện tại không có điểm danh, chỉ tạo ngoại lệ khi có thay đổi)
                $rand = rand(1, 100);

                if ($rand <= 65) {
                    // 65% buổi học diễn ra bình thường -> KHÔNG tạo điểm danh (mặc định không điểm danh)
                    continue;
                } elseif ($rand <= 80) {
                    // Case 1: Dời lịch (Reschedule) & đã dạy bù hoàn thành
                    $makeupDate = (clone $date)->addDays(rand(1, 4));
                    if ($makeupDate->dayOfWeek === Carbon::SUNDAY) {
                        $makeupDate->addDay();
                    }

                    ScheduleException::create([
                        'schedule_id' => $schedule->id,
                        'exception_date' => $dateStr,
                        'new_exception_date' => $makeupDate->toDateString(),
                        'action' => 'reschedule',
                        'new_start_time' => '17:30:00',
                        'new_end_time' => '19:00:00',
                        'reason' => 'Học sinh bị ốm, xin dời lịch và dạy bù',
                    ]);
                } elseif ($rand <= 88) {
                    // Case 2: Dời lịch (Reschedule) & đã hẹn bù nhưng CHƯA DẠY
                    $makeupDate = (clone $date)->addDays(rand(2, 5));
                    if ($makeupDate->dayOfWeek === Carbon::SUNDAY) {
                        $makeupDate->addDay();
                    }

                    ScheduleException::create([
                        'schedule_id' => $schedule->id,
                        'exception_date' => $dateStr,
                        'new_exception_date' => $makeupDate->toDateString(),
                        'action' => 'reschedule',
                        'new_start_time' => '18:00:00',
                        'new_end_time' => '19:30:00',
                        'reason' => 'Phụ huynh bận việc gia đình, xin hẹn dời lịch bù',
                    ]);
                } elseif ($rand <= 94) {
                    // Case 3: Hủy buổi học (Cancel)
                    ScheduleException::create([
                        'schedule_id' => $schedule->id,
                        'exception_date' => $dateStr,
                        'action' => 'cancel',
                        'cancel_actor' => 'student',
                        'reason' => 'Học sinh vắng không phép / Hủy buổi học',
                    ]);
                } else {
                    // Case 4: Dạy thay (Substitute)
                    $otherEmp = $employees->where('id', '!=', $schedule->employee_id)->first();
                    if ($otherEmp) {
                        ScheduleException::create([
                            'schedule_id' => $schedule->id,
                            'exception_date' => $dateStr,
                            'action' => 'substitute',
                            'new_employee_id' => $otherEmp->id,
                            'reason' => 'Giáo viên chính bận việc, nhờ giáo viên khác dạy thay',
                        ]);
                    }
                }
            }
        }
    }
}
