<?php

namespace Quochao56\Employee\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Quochao56\Employee\Models\EmployeeAttendance;
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

        $dayOfWeek = $date->dayOfWeek; // 0=Sun, 1=Mon, ... 6=Sat

        $this->info("Kiểm tra vắng mặt ngày: {$date->format('d/m/Y')} (thứ {$dayOfWeek})");

        // Lấy danh sách nhân viên có lịch trong ngày hôm nay (JSON array day_of_week)
        $scheduledEmployeeIds = Schedule::where('status', 'active')
            ->where(function ($q) use ($date, $dayOfWeek) {
                $q->whereJsonContains('day_of_week', $dayOfWeek)
                    ->where('start_date', '<=', $date->toDateString())
                    ->where(function ($q2) use ($date) {
                        $q2->whereNull('end_date')
                            ->orWhere('end_date', '>=', $date->toDateString());
                    });
            })
            ->pluck('employee_id')
            ->unique()
            ->values();

        if ($scheduledEmployeeIds->isEmpty()) {
            $this->info('Không có nhân viên nào có lịch làm hôm nay.');

            return self::SUCCESS;
        }

        // Lấy các đơn xin nghỉ phép đã được duyệt trong ngày
        $leaveRequests = \Quochao56\Employee\Models\LeaveRequest::where('status', 'approved')
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->get();

        // Lấy danh sách nhân viên đã check-in trong ngày
        $checkedInEmployeeIds = EmployeeAttendance::whereDate('date', $date->toDateString())
            ->whereNotNull('check_in_at')
            ->pluck('employee_id')
            ->unique();

        // Nhân viên vắng = có lịch nhưng chưa check-in
        $absentEmployeeIds = $scheduledEmployeeIds->diff($checkedInEmployeeIds);

        if ($absentEmployeeIds->isEmpty()) {
            $this->info('Tất cả nhân viên có lịch đều đã check-in. ✓');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($absentEmployeeIds as $employeeId) {
            $leave = $leaveRequests->where('employee_id', $employeeId)->first();
            
            // Nếu có phép nguyên ngày đã được duyệt, hệ thống đã tạo sẵn on_leave, không đánh vắng
            if ($leave && ! $leave->half_day) {
                continue;
            }

            // Nếu không có phép hoặc chỉ nghỉ nửa buổi mà không check-in -> đánh vắng
            $record = EmployeeAttendance::firstOrNew([
                'employee_id' => $employeeId,
                'date' => $date->toDateString(),
            ]);

            $noteAppend = trans('packages.employee::employee_attendance.notes.auto_absent');

            $record->check_in_at = null;
            $record->check_out_at = null;
            $record->status = 'absent';
            $record->flagged_missing_checkin = true;
            
            if (! str_contains($record->notes ?? '', $noteAppend)) {
                $record->notes = $record->notes ? $record->notes . ' | ' . $noteAppend : $noteAppend;
            }
            
            $record->save();

            $count++;
        }

        $this->info("Đã đánh dấu vắng mặt tự động cho {$count} nhân viên.");

        return self::SUCCESS;
    }
}
