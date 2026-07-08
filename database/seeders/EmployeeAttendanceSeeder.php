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

        if ($employees->isEmpty()) {
            return;
        }

        // Clean out existing data to seed cleanly
        EmployeeAttendance::truncate();
        LeaveRequest::truncate();
        AttendanceCorrectionRequest::truncate();
        StudentLeaveRequest::truncate();

        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        foreach ($employees as $empIndex => $employee) {
            // 1. Tạo 2 đơn nghỉ phép đã được duyệt trong những ngày gần đây (trong tháng hiện tại)
            $leaveDate1 = Carbon::now()->subDays(2 + ($empIndex * 3));
            if ($leaveDate1->dayOfWeek === Carbon::SUNDAY) {
                $leaveDate1->subDay();
            }
            $leave1 = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type' => 'annual',
                'start_date' => $leaveDate1->toDateString(),
                'end_date' => $leaveDate1->toDateString(),
                'half_day' => false,
                'reason' => 'Giải quyết công việc gia đình riêng',
                'status' => 'approved',
                'approved_by' => $adminUser?->id,
                'approved_at' => (clone $leaveDate1)->subDays(2),
            ]);

            // Tạo bản ghi chấm công nghỉ phép cả ngày cho phép này
            foreach (['morning', 'afternoon'] as $sess) {
                EmployeeAttendance::create([
                    'employee_id' => $employee->id,
                    'date' => $leaveDate1->toDateString(),
                    'session' => $sess,
                    'status' => 'on_leave',
                    'notes' => 'Nghỉ phép cả ngày: '.$leave1->reason,
                ]);
            }

            $leaveDate2 = Carbon::now()->subDays(5 + ($empIndex * 3));
            if ($leaveDate2->dayOfWeek === Carbon::SUNDAY) {
                $leaveDate2->subDay();
            }
            $leave2 = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type' => 'sick',
                'start_date' => $leaveDate2->toDateString(),
                'end_date' => $leaveDate2->toDateString(),
                'half_day' => true,
                'half_day_session' => 'morning',
                'reason' => 'Bị sốt đi khám bệnh',
                'status' => 'approved',
                'approved_by' => $adminUser?->id,
                'approved_at' => (clone $leaveDate2)->subDays(1),
            ]);

            // Nghỉ phép ca sáng
            EmployeeAttendance::create([
                'employee_id' => $employee->id,
                'date' => $leaveDate2->toDateString(),
                'session' => 'morning',
                'status' => 'on_leave',
                'notes' => 'Nghỉ phép nửa buổi (Sáng): '.$leave2->reason,
            ]);

            // Ca chiều đi làm bình thường
            EmployeeAttendance::create([
                'employee_id' => $employee->id,
                'date' => $leaveDate2->toDateString(),
                'session' => 'afternoon',
                'check_in_at' => $leaveDate2->toDateString().' 13:28:00',
                'check_out_at' => $leaveDate2->toDateString().' 17:35:00',
                'status' => 'present',
                'total_hours' => 4.0,
                'notes' => 'Đi làm ca chiều sau khi khám bệnh xong',
            ]);

            // 2. Chạy lịch sử chấm công cho các ngày còn lại
            for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
                if ($date->dayOfWeek === Carbon::SUNDAY) {
                    continue;
                }

                $dateStr = $date->toDateString();

                // Tránh ghi đè các ngày đã nghỉ phép ở trên
                if ($dateStr === $leaveDate1->toDateString() || $dateStr === $leaveDate2->toDateString()) {
                    continue;
                }

                $rand = rand(1, 100);

                if ($rand <= 75) {
                    // CASE 1: Đi làm đầy đủ giờ hành chính (Sáng + Chiều) -> Tạo 2 bản ghi riêng biệt
                    EmployeeAttendance::create([
                        'employee_id' => $employee->id,
                        'date' => $dateStr,
                        'session' => 'morning',
                        'check_in_at' => $dateStr.' 07:45:00',
                        'check_out_at' => $dateStr.' 12:00:00',
                        'status' => 'present',
                        'total_hours' => 4.0,
                        'check_in_latitude' => 10.776889,
                        'check_in_longitude' => 106.700806,
                        'check_out_latitude' => 10.776889,
                        'check_out_longitude' => 106.700806,
                        'notes' => 'Check-in đúng giờ hành chính',
                    ]);

                    EmployeeAttendance::create([
                        'employee_id' => $employee->id,
                        'date' => $dateStr,
                        'session' => 'afternoon',
                        'check_in_at' => $dateStr.' 13:30:00',
                        'check_out_at' => $dateStr.' 17:30:00',
                        'status' => 'present',
                        'total_hours' => 4.00,
                        'check_in_latitude' => 10.776889,
                        'check_in_longitude' => 106.700806,
                        'check_out_latitude' => 10.776889,
                        'check_out_longitude' => 106.700806,
                    ]);
                } elseif ($rand <= 85) {
                    // CASE 2: Làm thông từ sáng đến tối (Sáng + Chiều + Tối) -> Tạo 3 bản ghi (tự động tách)
                    EmployeeAttendance::create([
                        'employee_id' => $employee->id,
                        'date' => $dateStr,
                        'session' => 'morning',
                        'check_in_at' => $dateStr.' 07:30:00',
                        'check_out_at' => $dateStr.' 12:00:00',
                        'status' => 'present',
                        'total_hours' => 4.0,
                        'auto_closed' => true,
                        'notes' => 'Hệ thống tự động checkout cuối ca sáng (làm liên ca)',
                    ]);

                    EmployeeAttendance::create([
                        'employee_id' => $employee->id,
                        'date' => $dateStr,
                        'session' => 'afternoon',
                        'check_in_at' => $dateStr.' 13:30:00',
                        'check_out_at' => $dateStr.' 17:30:00',
                        'status' => 'present',
                        'total_hours' => 4.0,
                        'auto_closed' => true,
                        'notes' => 'Hệ thống tự động check-in/out ca chiều (làm liên ca)',
                    ]);

                    EmployeeAttendance::create([
                        'employee_id' => $employee->id,
                        'date' => $dateStr,
                        'session' => 'evening',
                        'check_in_at' => $dateStr.' 18:00:00',
                        'check_out_at' => $dateStr.' 20:00:00',
                        'status' => 'present',
                        'total_hours' => 2.0,
                        'notes' => 'Hoàn thành ca tối thực tế',
                    ]);
                } elseif ($rand <= 90) {
                    // CASE 3: Đi muộn ca sáng (Late)
                    EmployeeAttendance::create([
                        'employee_id' => $employee->id,
                        'date' => $dateStr,
                        'session' => 'morning',
                        'check_in_at' => $dateStr.' 08:35:00', // đi muộn 35p
                        'check_out_at' => $dateStr.' 12:00:00',
                        'status' => 'late',
                        'total_hours' => 3.42,
                        'notes' => 'Đi muộn do kẹt xe cầu Sài Gòn',
                    ]);

                    EmployeeAttendance::create([
                        'employee_id' => $employee->id,
                        'date' => $dateStr,
                        'session' => 'afternoon',
                        'check_in_at' => $dateStr.' 13:25:00',
                        'check_out_at' => $dateStr.' 17:30:00',
                        'status' => 'present',
                        'total_hours' => 4.0,
                    ]);
                } elseif ($rand <= 90) {
                    // CASE 4: Vắng mặt tự động (Absent)
                    foreach (['morning', 'afternoon'] as $sess) {
                        EmployeeAttendance::create([
                            'employee_id' => $employee->id,
                            'date' => $dateStr,
                            'session' => $sess,
                            'status' => 'absent',
                            'flagged_missing_checkin' => true,
                            'notes' => 'Vắng mặt tự động: Không thực hiện check-in ca làm việc.',
                        ]);
                    }
                } else {
                    // CASE 5: Chấm công sai vị trí (Flagged Location)
                    EmployeeAttendance::create([
                        'employee_id' => $employee->id,
                        'date' => $dateStr,
                        'session' => 'morning',
                        'check_in_at' => $dateStr.' 07:55:00',
                        'check_out_at' => $dateStr.' 12:00:00',
                        'status' => 'present',
                        'total_hours' => 4.0,
                        'check_in_latitude' => 10.123456, // Tọa độ sai hoàn toàn
                        'check_in_longitude' => 106.123456,
                        'flagged_location' => true,
                        'verification_status' => 'pending',
                        'notes' => 'Check-in ngoài vùng định vị cho phép.',
                    ]);
                }

                // CASE 6: Tạo thêm chấm công ca tối cố định cho Thứ 2, Thứ 4, Thứ 6
                if (in_array($date->dayOfWeek, [1, 3, 5]) && !($rand > 75 && $rand <= 85)) { // Tránh trùng với CASE 2 (76-85) đã tạo ca tối
                    if (rand(1, 100) <= 80) { // 80% đi dạy đầy đủ ca tối
                        EmployeeAttendance::create([
                            'employee_id' => $employee->id,
                            'date' => $dateStr,
                            'session' => 'evening',
                            'check_in_at' => $dateStr.' 18:00:00',
                            'check_out_at' => $dateStr.' 20:00:00',
                            'status' => 'present',
                            'total_hours' => 2.0,
                            'notes' => 'Chấm công ca tối dạy học.',
                        ]);
                    }
                }
            }

            // 3. Tạo một số đề xuất sửa công
            AttendanceCorrectionRequest::create([
                'employee_id' => $employee->id,
                'attendance_date' => Carbon::now()->subDays(2)->toDateString(),
                'requested_check_in_at' => Carbon::now()->subDays(2)->setTime(7, 50, 0)->toDateTimeString(),
                'requested_check_out_at' => Carbon::now()->subDays(2)->setTime(17, 30, 0)->toDateTimeString(),
                'reason' => 'Hệ thống điện thoại bị sập nguồn không bấm check-out được.',
                'status' => 'pending',
            ]);
        }

        // 4. Tạo đề xuất xin nghỉ phép của học sinh
        $students = Student::all();
        foreach ($students as $stuIndex => $student) {
            StudentLeaveRequest::create([
                'student_id' => $student->id,
                'start_date' => Carbon::now()->addDays(2)->toDateString(),
                'end_date' => Carbon::now()->addDays(3)->toDateString(),
                'reason' => 'Gia đình đi nghỉ mát hè tại Nha Trang',
                'created_by' => $adminUser?->id,
            ]);

            StudentLeaveRequest::create([
                'student_id' => $student->id,
                'start_date' => Carbon::now()->subDays(10)->toDateString(),
                'end_date' => Carbon::now()->subDays(9)->toDateString(),
                'reason' => 'Học sinh bị sốt siêu vi trùng',
                'created_by' => $adminUser?->id,
            ]);
        }
    }
}
