<?php

namespace Quochao56\Employee\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Scheduler\Models\Schedule;

class EmployeeAttendance extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    public const SESSION_MORNING = 'morning';

    public const SESSION_AFTERNOON = 'afternoon';

    public const SESSION_EVENING = 'evening';

    protected $table = 'employee_attendances';

    protected $fillable = [
        'employee_id',
        'date',
        'session',
        'check_in_at',
        'check_out_at',
        'status',
        'check_in_ip',
        'check_out_ip',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'total_hours',
        'notes',
        // Verification & geofencing
        'flagged_location',
        'verification_status',
        'unassigned_schedule',
        'schedule_id',
        // Open session / auto-close
        'auto_closed',
        'flagged_missing_checkin',
        'flagged_missing_checkout',
        // Correction tracking
        'corrected_by',
        'corrected_at',
    ];

    protected $casts = [
        'date' => 'date',
        'session' => 'string',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'corrected_at' => 'datetime',
        'total_hours' => 'decimal:2',
        'flagged_location' => 'boolean',
        'unassigned_schedule' => 'boolean',
        'auto_closed' => 'boolean',
        'flagged_missing_checkin' => 'boolean',
        'flagged_missing_checkout' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }

    public function scopePendingVerification($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeOpenSession($query)
    {
        return $query->whereNull('check_out_at');
    }

    public static function resolveSessionForDateTime(?Carbon $dateTime = null): string
    {
        $dateTime ??= now();

        $currentTime = Carbon::createFromFormat('H:i:s', $dateTime->format('H:i:s'));
        $morningEnd = Carbon::createFromTimeString((string) (settings('office_morning_end') ?? '12:00:00'));
        $eveningStart = Carbon::createFromTimeString((string) (settings('office_evening_start') ?? '18:00:00'));

        if ($currentTime->lte($morningEnd)) {
            return self::SESSION_MORNING;
        }

        if ($currentTime->lt($eveningStart)) {
            return self::SESSION_AFTERNOON;
        }

        return self::SESSION_EVENING;
    }

    /**
     * Tính tổng số giờ làm việc dựa trên check-in, check-out và session.
     */
    public static function calculateTotalHours(int $employeeId, Carbon $date, ?Carbon $checkInAt, ?Carbon $checkOutAt, string $checkInSession): ?float
    {
        if (! $checkInAt || ! $checkOutAt) {
            return null;
        }

        $morningStartStr = settings('office_morning_start', '08:00');
        $morningEndStr = settings('office_morning_end', '12:00');
        $afternoonStartStr = settings('office_afternoon_start', '13:30');
        $afternoonEndStr = settings('office_afternoon_end', '17:30');
        $eveningStartStr = settings('office_evening_start', '18:00');

        $morningStart = Carbon::parse($date->toDateString().' '.$morningStartStr);
        $morningEnd = Carbon::parse($date->toDateString().' '.$morningEndStr);
        $afternoonStart = Carbon::parse($date->toDateString().' '.$afternoonStartStr);
        $afternoonEnd = Carbon::parse($date->toDateString().' '.$afternoonEndStr);

        $checkInSession = self::resolveSessionForDateTime($checkInAt);
        $checkoutSession = self::resolveSessionForDateTime($checkOutAt);

        // Clamp CheckIn
        if ($checkInSession === self::SESSION_MORNING) {
            $effectiveCheckIn = $checkInAt->copy()->max($morningStart);
        } elseif ($checkInSession === self::SESSION_AFTERNOON) {
            $effectiveCheckIn = $checkInAt->copy()->max($afternoonStart);
        } else {
            $eveningStart = Carbon::parse($date->toDateString().' '.$eveningStartStr);
            $effectiveCheckIn = $checkInAt->copy()->max($eveningStart);
        }

        // Clamp CheckOut
        if ($checkoutSession === self::SESSION_MORNING) {
            $effectiveCheckOut = $checkOutAt->copy()->min($morningEnd);
        } elseif ($checkoutSession === self::SESSION_AFTERNOON) {
            $effectiveCheckOut = $checkOutAt->copy()->min($afternoonEnd);
        } else {
            $effectiveCheckOut = $checkOutAt->copy();
        }

        // Trường hợp 1: Check-out ở ca tối (Từ 18:00 trở đi)
        if ($checkoutSession === self::SESSION_EVENING) {
            $eveningHours = class_exists(Schedule::class)
                ? Schedule::getEveningTeachingHoursForEmployeeOnDate($employeeId, $date)
                : 0.0;

            // Nếu không có ca dạy tối: Quy về trường hợp Check-out bình thường ở cuối ca chiều (17:30)
            if ($eveningHours <= 0) {
                $effectiveCheckOut = $afternoonEnd;
                $checkoutSession = self::SESSION_AFTERNOON;
            } else {
                $morningHours = min(4.0, max(0.0, round($morningEnd->diffInMinutes($morningStart) / 60, 2)));
                if ($morningHours <= 0) {
                    $morningHours = 4.0;
                }

                $afternoonHours = min(4.0, max(0.0, round($afternoonEnd->diffInMinutes($afternoonStart) / 60, 2)));
                if ($afternoonHours <= 0) {
                    $afternoonHours = 4.0;
                }

                if ($checkInSession === self::SESSION_MORNING) {
                    return $morningHours + $afternoonHours + $eveningHours;
                } elseif ($checkInSession === self::SESSION_AFTERNOON) {
                    return $afternoonHours + $eveningHours;
                } else {
                    return $eveningHours;
                }
            }
        }

        // Trường hợp 2: Check-out ở ca ngày (Sáng hoặc Chiều)
        if ($checkInSession === self::SESSION_MORNING && $checkoutSession === self::SESSION_AFTERNOON) {
            // Check-in ca sáng nhưng Check-out ca chiều
            $morningHours = min(4.0, max(0.0, round(abs($morningEnd->diffInMinutes($effectiveCheckIn)) / 60, 2)));
            $afternoonHours = min(4.0, max(0.0, round(abs($effectiveCheckOut->diffInMinutes($afternoonStart)) / 60, 2)));

            return $morningHours + $afternoonHours;
        }

        // Các trường hợp khác (cùng trong ca sáng, hoặc cùng trong ca chiều): Tính thời gian thực tế
        $hours = round(abs($effectiveCheckOut->diffInMinutes($effectiveCheckIn)) / 60, 2);

        return min(4.0, max(0.0, $hours));
    }
}
