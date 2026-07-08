<?php

namespace Quochao56\Employee\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Quochao56\Employee\Models\EmployeeAttendance;
use Quochao56\Employee\Models\LeaveRequest;
use Quochao56\Scheduler\Models\Schedule;

class DetectAbsentEmployeesCommand extends Command
{
    protected $signature = 'attendance:detect-absent {--date= : Ngày cần kiểm tra (Y-m-d), mặc định hôm nay}';

    protected $description = 'Phát hiện Giáo viên/CTV vắng mặt không check-in và gắn cờ cảnh báo';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::today();

        $dayOfWeek = $date->dayOfWeek === 0 ? 1 : ($date->dayOfWeek + 1);

        $this->info("Kiểm tra vắng mặt ngày: {$date->format('d/m/Y')} (thứ {$dayOfWeek})");

        // Lấy danh sách tất cả các lịch học active trong ngày hôm nay
        $schedules = Schedule::where('status', 'active')
            ->where(function ($q) use ($date, $dayOfWeek) {
                $q->whereJsonContains('day_of_week', $dayOfWeek)
                    ->where('start_date', '<=', $date->toDateString())
                    ->where(function ($q2) use ($date) {
                        $q2->whereNull('end_date')
                            ->orWhere('end_date', '>=', $date->toDateString());
                    });
            })
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('Không có giáo viên nào có lịch làm hôm nay.');

            return self::SUCCESS;
        }

        // Lấy các đơn xin nghỉ phép đã được duyệt trong ngày
        $leaveRequests = LeaveRequest::where('status', 'approved')
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->get();

        // Nhóm lịch học theo giáo viên
        $employeeSchedules = $schedules->groupBy('employee_id');
        $count = 0;

        foreach ($employeeSchedules as $employeeId => $schedulesForEmployee) {
            // Xác định các session (ca làm) mà giáo viên này có lịch
            $scheduledSessions = $schedulesForEmployee->map(function ($schedule) use ($date) {
                $dateTime = Carbon::parse($date->toDateString().' '.$schedule->start_time);

                return EmployeeAttendance::resolveSessionForDateTime($dateTime);
            })->unique()->values();

            $employeeLeaves = $leaveRequests->where('employee_id', $employeeId);

            foreach ($scheduledSessions as $session) {
                // Kiểm tra xem giáo viên có phép cho ca này không (nguyên ngày hoặc nửa buổi trùng ca)
                $hasExcusedLeave = $employeeLeaves->contains(function ($leave) use ($session) {
                    if (! $leave->half_day) {
                        return true; // Nghỉ phép cả ngày -> miễn trừ tất cả các ca
                    }

                    return $leave->half_day_session === $session; // Nghỉ nửa buổi ca tương ứng
                });

                if ($hasExcusedLeave) {
                    continue;
                }

                // Kiểm tra xem giáo viên đã check-in cho ca này chưa,
                // hoặc đang có một phiên làm việc dở dang (chưa check-out) trong ngày (trường hợp làm liên ca từ ca trước hoặc quên check-out)
                $hasCheckInOrOpenSession = EmployeeAttendance::where('employee_id', $employeeId)
                    ->whereDate('date', $date->toDateString())
                    ->whereNotNull('check_in_at')
                    ->where(function ($q) use ($session) {
                        $q->where('session', $session)
                            ->orWhereNull('check_out_at');
                    })
                    ->exists();

                if ($hasCheckInOrOpenSession) {
                    continue;
                }

                // Đánh dấu vắng mặt cho ca này
                $record = EmployeeAttendance::firstOrNew([
                    'employee_id' => $employeeId,
                    'date' => $date->toDateString(),
                    'session' => $session,
                ]);

                // Nếu bản ghi đã tồn tại và đã check-in thì bỏ qua
                if ($record->exists && $record->check_in_at !== null) {
                    continue;
                }

                $noteAppend = trans('packages.employee::employee_attendance.notes.auto_absent');

                $record->check_in_at = null;
                $record->check_out_at = null;
                $record->status = 'absent';
                $record->flagged_missing_checkin = true;

                if (! str_contains($record->notes ?? '', $noteAppend)) {
                    $record->notes = $record->notes ? $record->notes.' | '.$noteAppend : $noteAppend;
                }

                $record->save();
                $count++;
            }
        }

        $this->info("Đã đánh dấu vắng mặt tự động cho {$count} ca làm việc của giáo viên.");

        return self::SUCCESS;
    }
}
