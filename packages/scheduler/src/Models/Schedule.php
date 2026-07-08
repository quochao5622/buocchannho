<?php

namespace Quochao56\Scheduler\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Employee\Models\Employee;
use Quochao56\Student\Models\Student;

class Schedule extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'schedules';

    protected $fillable = [
        'student_id',
        'employee_id',
        'classroom_id',
        'title',
        'type',
        'day_of_week',
        'start_time',
        'end_time',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'day_of_week' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'string',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ScheduleException::class, 'schedule_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Tìm buổi học của giáo viên trùng khớp/gần nhất trong ngày hiện tại.
     * Hỗ trợ giải quyết dời lịch, dạy thay, và hủy lịch của ngày hôm đó.
     */
    public static function findMatchingScheduleForEmployee(int $employeeId, Carbon $dateTime): ?self
    {
        $dateStr = $dateTime->toDateString();
        $dayOfWeek = $dateTime->dayOfWeek === 0 ? 1 : ($dateTime->dayOfWeek + 1);

        // Lấy tất cả lịch học có thể áp dụng cho giáo viên này hôm nay
        $schedules = self::active()
            ->where(function ($q) use ($employeeId, $dateStr) {
                // Giáo viên chính thức của lịch học
                $q->where('employee_id', $employeeId)
                    // Hoặc giáo viên được phân dạy thay hôm nay
                    ->orWhereHas('exceptions', function ($sub) use ($employeeId, $dateStr) {
                        $sub->where(function ($dateQuery) use ($dateStr) {
                            $dateQuery->whereDate('exception_date', $dateStr)
                                ->orWhereDate('new_exception_date', $dateStr);
                        })
                            ->where('action', 'substitute')
                            ->where('new_employee_id', $employeeId);
                    });
            })
            ->whereDate('start_date', '<=', $dateStr)
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $dateStr);
            })
            ->with(['exceptions' => function ($q) use ($dateStr) {
                $q->where(function ($dateQuery) use ($dateStr) {
                    $dateQuery->whereDate('exception_date', $dateStr)
                        ->orWhereDate('new_exception_date', $dateStr);
                });
            }])
            ->get();

        $matchedSchedule = null;
        $minDiff = 999999;

        foreach ($schedules as $schedule) {
            $exception = $schedule->exceptions
                ->sortByDesc(function ($item) use ($dateStr) {
                    if ($item->action === 'reschedule' && $item->new_exception_date?->toDateString() === $dateStr) {
                        return 2;
                    }

                    return $item->exception_date?->toDateString() === $dateStr ? 1 : 0;
                })
                ->first();

            if (
                $exception
                && $exception->action === 'reschedule'
                && $exception->new_exception_date
                && $exception->exception_date?->toDateString() === $dateStr
                && $exception->new_exception_date->toDateString() !== $dateStr
            ) {
                continue;
            }

            // Nếu lịch bị hủy hôm nay
            if ($exception && $exception->action === 'cancel') {
                continue;
            }

            // Nếu có dạy thay nhưng giáo viên dạy thay không phải $employeeId
            if ($exception && $exception->action === 'substitute' && $exception->new_employee_id != $employeeId) {
                continue;
            }

            // Nếu không có dạy thay hôm nay nhưng giáo viên phụ trách gốc không phải $employeeId (đã bị người khác dạy thay)
            if ((! $exception || $exception->action !== 'substitute') && $schedule->employee_id != $employeeId) {
                continue;
            }

            // Kiểm tra thứ trong tuần (trừ khi có ngoại lệ dời lịch sang hôm nay)
            $isExceptionReschedule = $exception
                && $exception->action === 'reschedule'
                && (
                    $exception->new_exception_date?->toDateString() === $dateStr
                    || $exception->exception_date?->toDateString() === $dateStr
                );
            if (! $isExceptionReschedule) {
                $days = $schedule->day_of_week;
                if (is_string($days)) {
                    $days = json_decode($days, true);
                }
                if (! empty($days) && ! in_array($dayOfWeek, (array) $days)) {
                    continue;
                }
            }

            // Xác định giờ bắt đầu (giờ dời lịch hoặc giờ mặc định)
            $startTime = ($exception && $exception->action === 'reschedule' && $exception->new_start_time)
                ? $exception->new_start_time
                : $schedule->start_time;

            // Tính chênh lệch phút
            $scheduleStart = Carbon::parse($dateStr.' '.$startTime);
            $diffInMinutes = abs($dateTime->diffInMinutes($scheduleStart));

            // Cho phép check-in sớm trước giờ học hoặc muộn sau giờ học theo cấu hình trong Settings
            $earlyMinutes = (int) settings('schedule_early_checkin_minutes', 30);
            $lateMinutes = (int) settings('schedule_late_checkin_minutes', 30);

            $isEarlyOrOnTime = $dateTime->lte($scheduleStart) && $diffInMinutes <= $earlyMinutes;
            $isAcceptableLate = $dateTime->gt($scheduleStart) && $diffInMinutes <= $lateMinutes;

            if (($isEarlyOrOnTime || $isAcceptableLate) && $diffInMinutes < $minDiff) {
                $minDiff = $diffInMinutes;
                $matchedSchedule = $schedule;
            }
        }

        return $matchedSchedule;
    }

    /**
     * Tính tổng thời lượng (giờ) giảng dạy buổi tối của giáo viên trong ngày cụ thể.
     */
    public static function getEveningTeachingHoursForEmployeeOnDate(int $employeeId, Carbon $date): float
    {
        $dateStr = $date->toDateString();
        $dayOfWeek = $date->dayOfWeek === 0 ? 1 : ($date->dayOfWeek + 1);
        $eveningStartStr = settings('office_evening_start', '18:00');

        // Lấy tất cả lịch học active có liên quan đến giáo viên này
        $schedules = self::active()
            ->where(function ($q) use ($employeeId, $dateStr) {
                $q->where('employee_id', $employeeId)
                    ->orWhereHas('exceptions', function ($sub) use ($employeeId, $dateStr) {
                        $sub->where(function ($dateQuery) use ($dateStr) {
                            $dateQuery->whereDate('exception_date', $dateStr)
                                ->orWhereDate('new_exception_date', $dateStr);
                        })
                            ->where('action', 'substitute')
                            ->where('new_employee_id', $employeeId);
                    });
            })
            ->whereDate('start_date', '<=', $dateStr)
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $dateStr);
            })
            ->with(['exceptions' => function ($q) use ($dateStr) {
                $q->where(function ($dateQuery) use ($dateStr) {
                    $dateQuery->whereDate('exception_date', $dateStr)
                        ->orWhereDate('new_exception_date', $dateStr);
                });
            }])
            ->get();

        $totalHours = 0.0;

        foreach ($schedules as $schedule) {
            $exception = $schedule->exceptions
                ->sortByDesc(function ($item) use ($dateStr) {
                    if ($item->action === 'reschedule' && $item->new_exception_date?->toDateString() === $dateStr) {
                        return 2;
                    }

                    return $item->exception_date?->toDateString() === $dateStr ? 1 : 0;
                })
                ->first();

            // Nếu lịch bị dời đi ngày khác
            if (
                $exception
                && $exception->action === 'reschedule'
                && $exception->new_exception_date
                && $exception->exception_date?->toDateString() === $dateStr
                && $exception->new_exception_date->toDateString() !== $dateStr
            ) {
                continue;
            }

            // Nếu lịch bị hủy hôm nay
            if ($exception && $exception->action === 'cancel') {
                continue;
            }

            // Nếu có dạy thay nhưng giáo viên dạy thay không phải $employeeId
            if ($exception && $exception->action === 'substitute' && $exception->new_employee_id != $employeeId) {
                continue;
            }

            // Nếu không có dạy thay hôm nay nhưng giáo viên phụ trách gốc không phải $employeeId (đã bị người khác dạy thay)
            if ((! $exception || $exception->action !== 'substitute') && $schedule->employee_id != $employeeId) {
                continue;
            }

            // Kiểm tra thứ trong tuần (trừ khi có ngoại lệ dời lịch sang hôm nay)
            $isExceptionReschedule = $exception
                && $exception->action === 'reschedule'
                && (
                    $exception->new_exception_date?->toDateString() === $dateStr
                    || $exception->exception_date?->toDateString() === $dateStr
                );
            if (! $isExceptionReschedule) {
                $days = $schedule->day_of_week;
                if (is_string($days)) {
                    $days = json_decode($days, true);
                }
                if (! empty($days) && ! in_array($dayOfWeek, (array) $days)) {
                    continue;
                }
            }

            // Xác định giờ bắt đầu và kết thúc
            $startTime = ($exception && $exception->action === 'reschedule' && $exception->new_start_time)
                ? $exception->new_start_time
                : $schedule->start_time;

            $endTime = ($exception && $exception->action === 'reschedule' && $exception->new_end_time)
                ? $exception->new_end_time
                : $schedule->end_time;

            // Kiểm tra nếu ca học thuộc về buổi tối (start_time >= evening_start)
            if ($startTime >= $eveningStartStr) {
                if ($startTime && $endTime) {
                    $startCarbon = Carbon::parse($dateStr.' '.$startTime);
                    $endCarbon = Carbon::parse($dateStr.' '.$endTime);
                    $diffHours = round(abs($endCarbon->diffInMinutes($startCarbon)) / 60, 2);
                    if ($diffHours > 0) {
                        $totalHours += $diffHours;
                    }
                }
            }
        }

        return $totalHours;
    }
}
