<?php

namespace Quochao56\Employee\Services;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Quochao56\Core\Models\User;
use Quochao56\Employee\Helpers\GeoFenceHelper;
use Quochao56\Employee\Models\Employee;
use Quochao56\Employee\Models\EmployeeAttendance;
use Quochao56\Employee\Models\LeaveRequest;
use Quochao56\Scheduler\Models\Schedule;

class AttendanceCheckinService
{
    /**
     * Thực hiện check-in cho giáo viên.
     * Tự động phát hiện và đóng phiên dở dang của ngày hôm trước nếu có.
     *
     * @param  array{
     *     latitude?: float,
     *     longitude?: float,
     *     ip?: string,
     *     notes?: string,
     * }  $locationData
     */
    public function checkIn(Employee $employee, array $locationData = []): EmployeeAttendance
    {
        return DB::transaction(function () use ($employee, $locationData) {
            // 1. Phát hiện và xử lý phiên dở dang từ ngày trước
            $this->closeOpenSessionIfExists($employee);

            $session = EmployeeAttendance::resolveSessionForDateTime(now());

            // 2. Xác thực vị trí
            $latitude = $locationData['latitude'] ?? null;
            $longitude = $locationData['longitude'] ?? null;
            $ip = $locationData['ip'] ?? request()->ip();

            // Validate geofence - may throw exception if location invalid and flagged location disabled
            $locationVerified = $this->validateGeofenceLocation($latitude, $longitude, $ip);

            // Nếu đang check-in ở ca mới, tự chốt các phiên cùng ngày nhưng thuộc ca trước đó
            $this->closeEarlierOpenAttendancesForCurrentCheckIn(
                $employee,
                $session,
                $latitude,
                $longitude,
                $ip,
                $locationVerified,
            );

            $status = $this->determineCheckInStatus($employee);

            $existing = EmployeeAttendance::where('employee_id', $employee->id)
                ->whereDate('date', now()->toDateString())
                ->where('session', $session)
                ->first();

            if ($existing && $existing->check_in_at) {
                return $existing;
            }

            // 3. Tạo bản ghi check-in mới (hoặc cập nhật nếu đã có bản ghi on_leave)
            $empAttendance = EmployeeAttendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => now()->toDateString(),
                    'session' => $session,
                ],
                [
                    'check_in_at' => now(),
                    'check_in_ip' => $ip,
                    'check_in_latitude' => $latitude,
                    'check_in_longitude' => $longitude,
                    'status' => $status,
                    'flagged_location' => ! $locationVerified,
                    'verification_status' => $locationVerified ? 'approved' : 'pending',
                    'notes' => $locationData['notes'] ?? null,
                ]
            );

            // Tự động điểm danh giáo viên vào buổi học trùng khớp
            if (class_exists(Schedule::class)) {
                try {
                    $schedule = Schedule::findMatchingScheduleForEmployee($employee->id, now());
                    if ($schedule) {
                        $attendanceModel = 'Quochao56\\Scheduler\\Models\\Attendance';

                        if (class_exists($attendanceModel)) {
                            $classAttendance = $attendanceModel::firstOrNew([
                                'schedule_id' => $schedule->id,
                                'attendance_date' => now()->toDateString(),
                                'student_id' => $schedule->student_id,
                            ]);

                            if (! $classAttendance->exists) {
                                $classAttendance->status = 'present';
                                $classAttendance->verified_by_employee_id = $employee->id;
                            }

                            $classAttendance->check_in_at = now();
                            $classAttendance->actual_employee_id = $employee->id;
                            $classAttendance->save();
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error('Auto-attendance checkin error: '.$e->getMessage());
                }
            }

            return $empAttendance;
        });
    }

    /**
     * Xác định trạng thái điểm danh (đúng giờ, đi muộn) dựa trên thời gian check-in
     * và đơn xin nghỉ phép nửa buổi (nếu có).
     */
    protected function determineCheckInStatus(Employee $employee): string
    {
        $today = now()->toDateString();
        $session = EmployeeAttendance::resolveSessionForDateTime(now());

        $leaveRequest = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($leaveRequest && $leaveRequest->half_day && $leaveRequest->half_day_session === 'morning' && $session === 'afternoon') {
            // Xin phép nghỉ sáng -> Giờ bắt đầu làm việc là đầu giờ chiều
            $expectedStartTime = settings('office_afternoon_start', '13:30');
        } elseif ($leaveRequest && $leaveRequest->half_day && $leaveRequest->half_day_session === 'afternoon' && $session === 'evening') {
            // Xin phép nghỉ chiều -> ưu tiên phiên tối nếu có cấu hình
            $expectedStartTime = settings('office_evening_start', '18:00');
        } else {
            $expectedStartTime = $this->resolveExpectedStartTimeByCurrentSession();
        }

        // Ưu tiên kiểm tra xem giáo viên có lịch dạy/ca học nào trùng khớp với thời điểm check-in không
        if (class_exists(Schedule::class)) {
            try {
                $matchingSchedule = Schedule::findMatchingScheduleForEmployee($employee->id, now());
                if ($matchingSchedule) {
                    $expectedStartTime = $matchingSchedule->start_time;
                }
            } catch (\Throwable $e) {
                Log::error('Check-in status schedule match error: '.$e->getMessage());
            }
        }

        $allowedLateMinutes = (int) settings('office_allowed_late_minutes', 0);
        $expectedStartCarbon = Carbon::parse($today.' '.$expectedStartTime);
        $lateThreshold = $expectedStartCarbon->copy()->addMinutes($allowedLateMinutes);

        if (now()->greaterThan($lateThreshold)) {
            return 'late';
        }

        return 'present';
    }

    protected function resolveExpectedStartTimeByCurrentSession(): string
    {
        $now = now();
        $morningStart = settings('office_morning_start', '08:00');
        $morningEnd = settings('office_morning_end', '12:00');
        $afternoonStart = settings('office_afternoon_start', '13:30');
        $afternoonEnd = settings('office_afternoon_end', '17:30');
        $eveningStart = settings('office_evening_start', '18:00');

        $current = Carbon::createFromFormat('H:i:s', $now->format('H:i:s'));
        $morningEndTime = Carbon::createFromTimeString((string) $morningEnd);
        $afternoonEndTime = Carbon::createFromTimeString((string) $afternoonEnd);

        if ($current->lte($morningEndTime)) {
            return $morningStart;
        }

        if ($current->lte($afternoonEndTime)) {
            return $afternoonStart;
        }

        return $eveningStart;
    }

    /**
     * Thực hiện check-out cho giáo viên.
     */
    public function checkOut(EmployeeAttendance $attendance, array $locationData = []): EmployeeAttendance
    {
        $latitude = $locationData['latitude'] ?? null;
        $longitude = $locationData['longitude'] ?? null;
        $ip = $locationData['ip'] ?? request()->ip();

        // Validate geofence - may throw exception if location invalid and flagged location disabled
        $locationVerified = $this->validateGeofenceLocation($latitude, $longitude, $ip);

        $checkIn = $attendance->check_in_at;
        $checkOut = now();

        $checkInSession = $attendance->session;
        $checkoutSession = EmployeeAttendance::resolveSessionForDateTime($checkOut);

        $finalAttendance = $attendance;

        if ($checkInSession === EmployeeAttendance::SESSION_MORNING && $checkoutSession === EmployeeAttendance::SESSION_AFTERNOON) {
            // Sáng -> Chiều
            $morningStartStr = settings('office_morning_start', '08:00');
            $morningStart = Carbon::parse($attendance->date->toDateString().' '.$morningStartStr);
            $effectiveCheckIn = $checkIn->copy()->max($morningStart);

            $morningEndStr = settings('office_morning_end', '12:00');
            $morningEnd = Carbon::parse($attendance->date->toDateString().' '.$morningEndStr);
            $morningHours = min(4.0, max(0.0, round(abs($morningEnd->diffInMinutes($effectiveCheckIn)) / 60, 2)));

            $attendance->update([
                'check_out_at' => $morningEnd,
                'check_out_ip' => $ip,
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
                'total_hours' => $morningHours,
                'auto_closed' => true,
                'notes' => 'Hệ thống tự động checkout cuối ca sáng',
                'flagged_location' => $attendance->flagged_location || ! $locationVerified,
                'verification_status' => ($attendance->flagged_location || ! $locationVerified) ? 'pending' : 'approved',
            ]);

            $afternoonStartStr = settings('office_afternoon_start', '13:30');
            $afternoonEndStr = settings('office_afternoon_end', '17:30');
            $afternoonStart = Carbon::parse($attendance->date->toDateString().' '.$afternoonStartStr);
            $afternoonEnd = Carbon::parse($attendance->date->toDateString().' '.$afternoonEndStr);
            $effectiveCheckOut = $checkOut->copy()->min($afternoonEnd);
            $afternoonHours = min(4.0, max(0.0, round(abs($effectiveCheckOut->diffInMinutes($afternoonStart)) / 60, 2)));

            $finalAttendance = EmployeeAttendance::create([
                'employee_id' => $attendance->employee_id,
                'date' => $attendance->date,
                'session' => EmployeeAttendance::SESSION_AFTERNOON,
                'check_in_at' => $afternoonStart,
                'check_out_at' => $checkOut,
                'check_in_ip' => $attendance->check_in_ip,
                'check_out_ip' => $ip,
                'check_in_latitude' => $attendance->check_in_latitude,
                'check_in_longitude' => $attendance->check_in_longitude,
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
                'total_hours' => $afternoonHours,
                'status' => $attendance->status,
                'flagged_location' => $attendance->flagged_location || ! $locationVerified,
                'verification_status' => ($attendance->flagged_location || ! $locationVerified) ? 'pending' : 'approved',
                'notes' => 'Hệ thống tự động check-in ca chiều (làm liên ca)',
            ]);
        } elseif ($checkInSession === EmployeeAttendance::SESSION_MORNING && $checkoutSession === EmployeeAttendance::SESSION_EVENING) {
            // Sáng -> Tối
            $morningStartStr = settings('office_morning_start', '08:00');
            $morningStart = Carbon::parse($attendance->date->toDateString().' '.$morningStartStr);
            $effectiveCheckIn = $checkIn->copy()->max($morningStart);

            $morningEndStr = settings('office_morning_end', '12:00');
            $morningEnd = Carbon::parse($attendance->date->toDateString().' '.$morningEndStr);
            $morningHours = min(4.0, max(0.0, round(abs($morningEnd->diffInMinutes($effectiveCheckIn)) / 60, 2)));

            $attendance->update([
                'check_out_at' => $morningEnd,
                'check_out_ip' => $ip,
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
                'total_hours' => $morningHours,
                'auto_closed' => true,
                'notes' => 'Hệ thống tự động checkout cuối ca sáng',
                'flagged_location' => $attendance->flagged_location || ! $locationVerified,
                'verification_status' => ($attendance->flagged_location || ! $locationVerified) ? 'pending' : 'approved',
            ]);

            $afternoonStartStr = settings('office_afternoon_start', '13:30');
            $afternoonEndStr = settings('office_afternoon_end', '17:30');
            $afternoonStart = Carbon::parse($attendance->date->toDateString().' '.$afternoonStartStr);
            $afternoonEnd = Carbon::parse($attendance->date->toDateString().' '.$afternoonEndStr);
            $afternoonHours = min(4.0, max(0.0, round(abs($afternoonEnd->diffInMinutes($afternoonStart)) / 60, 2)));

            EmployeeAttendance::create([
                'employee_id' => $attendance->employee_id,
                'date' => $attendance->date,
                'session' => EmployeeAttendance::SESSION_AFTERNOON,
                'check_in_at' => $afternoonStart,
                'check_out_at' => $afternoonEnd,
                'check_in_ip' => $attendance->check_in_ip,
                'check_out_ip' => $ip,
                'check_in_latitude' => $attendance->check_in_latitude,
                'check_in_longitude' => $attendance->check_in_longitude,
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
                'total_hours' => $afternoonHours,
                'status' => $attendance->status,
                'auto_closed' => true,
                'flagged_location' => $attendance->flagged_location || ! $locationVerified,
                'verification_status' => ($attendance->flagged_location || ! $locationVerified) ? 'pending' : 'approved',
                'notes' => 'Hệ thống tự động chấm công ca chiều (làm liên ca)',
            ]);

            $eveningStartStr = settings('office_evening_start', '18:00');
            $eveningStart = Carbon::parse($attendance->date->toDateString().' '.$eveningStartStr);
            $eveningHours = class_exists(Schedule::class)
                ? Schedule::getEveningTeachingHoursForEmployeeOnDate($attendance->employee_id, $attendance->date)
                : 0.0;

            $finalAttendance = EmployeeAttendance::create([
                'employee_id' => $attendance->employee_id,
                'date' => $attendance->date,
                'session' => EmployeeAttendance::SESSION_EVENING,
                'check_in_at' => $eveningStart,
                'check_out_at' => $checkOut,
                'check_in_ip' => $attendance->check_in_ip,
                'check_out_ip' => $ip,
                'check_in_latitude' => $attendance->check_in_latitude,
                'check_in_longitude' => $attendance->check_in_longitude,
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
                'total_hours' => $eveningHours,
                'status' => $attendance->status,
                'flagged_location' => $attendance->flagged_location || ! $locationVerified,
                'verification_status' => ($attendance->flagged_location || ! $locationVerified) ? 'pending' : 'approved',
                'notes' => 'Hệ thống tự động check-in ca tối (làm liên ca)',
            ]);
        } elseif ($checkInSession === EmployeeAttendance::SESSION_AFTERNOON && $checkoutSession === EmployeeAttendance::SESSION_EVENING) {
            // Chiều -> Tối
            $afternoonStartStr = settings('office_afternoon_start', '13:30');
            $afternoonStart = Carbon::parse($attendance->date->toDateString().' '.$afternoonStartStr);
            $effectiveCheckIn = $checkIn->copy()->max($afternoonStart);

            $afternoonEndStr = settings('office_afternoon_end', '17:30');
            $afternoonEnd = Carbon::parse($attendance->date->toDateString().' '.$afternoonEndStr);
            $afternoonHours = min(4.0, max(0.0, round(abs($afternoonEnd->diffInMinutes($effectiveCheckIn)) / 60, 2)));

            $attendance->update([
                'check_out_at' => $afternoonEnd,
                'check_out_ip' => $ip,
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
                'total_hours' => $afternoonHours,
                'auto_closed' => true,
                'notes' => 'Hệ thống tự động checkout cuối ca chiều',
                'flagged_location' => $attendance->flagged_location || ! $locationVerified,
                'verification_status' => ($attendance->flagged_location || ! $locationVerified) ? 'pending' : 'approved',
            ]);

            $eveningStartStr = settings('office_evening_start', '18:00');
            $eveningStart = Carbon::parse($attendance->date->toDateString().' '.$eveningStartStr);
            $eveningHours = class_exists(Schedule::class)
                ? Schedule::getEveningTeachingHoursForEmployeeOnDate($attendance->employee_id, $attendance->date)
                : 0.0;

            $finalAttendance = EmployeeAttendance::create([
                'employee_id' => $attendance->employee_id,
                'date' => $attendance->date,
                'session' => EmployeeAttendance::SESSION_EVENING,
                'check_in_at' => $eveningStart,
                'check_out_at' => $checkOut,
                'check_in_ip' => $attendance->check_in_ip,
                'check_out_ip' => $ip,
                'check_in_latitude' => $attendance->check_in_latitude,
                'check_in_longitude' => $attendance->check_in_longitude,
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
                'total_hours' => $eveningHours,
                'status' => $attendance->status,
                'flagged_location' => $attendance->flagged_location || ! $locationVerified,
                'verification_status' => ($attendance->flagged_location || ! $locationVerified) ? 'pending' : 'approved',
                'notes' => 'Hệ thống tự động check-in ca tối (làm liên ca)',
            ]);
        } else {
            // Cùng ca
            $totalHours = EmployeeAttendance::calculateTotalHours(
                $attendance->employee_id,
                $attendance->date,
                $checkIn,
                $checkOut,
                $attendance->session
            );

            $attendance->update([
                'check_out_at' => $checkOut,
                'check_out_ip' => $ip,
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
                'total_hours' => $totalHours,
                'flagged_location' => $attendance->flagged_location || ! $locationVerified,
                'verification_status' => ($attendance->flagged_location || ! $locationVerified) ? 'pending' : 'approved',
            ]);

            $finalAttendance = $attendance;
        }

        // Tự động điểm danh checkout cho buổi học của giáo viên
        $attendanceModel = 'Quochao56\\Scheduler\\Models\\Attendance';

        if (class_exists($attendanceModel)) {
            try {
                $classAttendance = $attendanceModel::query()->where('actual_employee_id', $attendance->employee_id)
                    ->whereDate('attendance_date', now()->toDateString())
                    ->whereNotNull('check_in_at')
                    ->whereNull('check_out_at')
                    ->first();

                if ($classAttendance) {
                    $classAttendance->check_out_at = now();
                    $chkIn = $classAttendance->check_in_at;
                    $classAttendance->total_hours = $chkIn ? round(abs(now()->diffInMinutes($chkIn)) / 60, 2) : null;
                    $classAttendance->save();
                }
            } catch (\Throwable $e) {
                Log::error('Auto-attendance checkout error: '.$e->getMessage());
            }
        }

        return $finalAttendance->fresh();
    }

    /**
     * Xác thực vị trí check-in dựa trên cấu hình geofence.
     *
     * @return bool True nếu vị trí hợp lệ hoặc nếu flagged location được phép
     *
     * @throws InvalidArgumentException Nếu vị trí không hợp lệ và flagged location bị disable
     */
    protected function validateGeofenceLocation(
        ?float $latitude,
        ?float $longitude,
        ?string $ip
    ): bool {
        $locationVerified = GeoFenceHelper::verifyLocation($latitude, $longitude, $ip);

        // Nếu vị trí hợp lệ, không cần xử lý
        if ($locationVerified) {
            return true;
        }

        // Nếu vị trí không hợp lệ và flagged location bị disable, throw exception
        if (! settings('allow_flagged_location', true)) {
            $centerLat = settings('center_latitude');
            $centerLon = settings('center_longitude');
            $radiusMeters = settings('allowed_radius_meters', 50);
            $distance = $latitude && $longitude
                ? GeoFenceHelper::distanceInMeters($latitude, $longitude, $centerLat, $centerLon)
                : null;

            throw new InvalidArgumentException(
                trans('packages.employee::employee_attendance.actions.outside_radius', [
                    'distance' => round($distance ?? 0),
                ])
            );
        }

        return false;
    }

    /**
     * Tìm và tự động đóng phiên check-in dở dang (quên checkout từ ngày trước).
     * Điều kiện: check_out_at = null VÀ check_in_at < hôm nay VÀ cách hiện tại > 20 tiếng
     * (để không đóng nhầm ca tăng ca hợp lệ qua đêm).
     */
    protected function closeOpenSessionIfExists(Employee $employee): void
    {
        /** @var EmployeeAttendance|null $openSession */
        $openSession = EmployeeAttendance::query()->where('employee_id', $employee->id)
            ->whereNull('check_out_at')
            ->whereDate('date', '<', now()->toDateString())
            ->where('check_in_at', '<', now()->subHours(20))
            ->latest('check_in_at')
            ->first();

        if (! $openSession) {
            return;
        }

        // Tự động đóng phiên với giờ checkout = auto_checkout_time của ngày cũ
        $autoCheckoutTime = settings('auto_checkout_time', '17:30');
        $autoCheckoutAt = Carbon::parse(
            $openSession->date->toDateString().' '.$autoCheckoutTime
        );

        $totalHours = $openSession->check_in_at
            ? round(abs($autoCheckoutAt->diffInMinutes($openSession->check_in_at)) / 60, 2)
            : null;

        $openSession->update([
            'check_out_at' => $autoCheckoutAt,
            'total_hours' => $totalHours > 0 ? $totalHours : null,
            'auto_closed' => true,
            'flagged_missing_checkout' => true,
        ]);

        // Tự động đóng điểm danh checkout cho buổi học dở dang ngày trước
        $attendanceModel = 'Quochao56\\Scheduler\\Models\\Attendance';

        if (class_exists($attendanceModel)) {
            try {
                $classAttendance = $attendanceModel::query()->where('actual_employee_id', $employee->id)
                    ->whereDate('attendance_date', $openSession->date->toDateString())
                    ->whereNotNull('check_in_at')
                    ->whereNull('check_out_at')
                    ->first();

                if ($classAttendance) {
                    $classAttendance->check_out_at = $autoCheckoutAt;
                    $chkIn = $classAttendance->check_in_at;
                    $classAttendance->total_hours = $chkIn ? round(abs($autoCheckoutAt->diffInMinutes($chkIn)) / 60, 2) : null;
                    $classAttendance->save();
                }
            } catch (\Throwable $e) {
                Log::error('Auto-attendance auto-close error: '.$e->getMessage());
            }
        }

        // Gửi thông báo Admin nếu được cấu hình
        if (settings('open_session_notify_admin', true)) {
            $this->notifyAdminAboutAutoClose($employee, $openSession);
        }
    }

    /**
     * Khi check-in ở ca mới, đóng các phiên cùng ngày đang mở nhưng thuộc ca trước đó.
     */
    protected function closeEarlierOpenAttendancesForCurrentCheckIn(
        Employee $employee,
        string $currentSession,
        ?float $latitude,
        ?float $longitude,
        ?string $ip,
        bool $locationVerified,
    ): void {
        $currentSessionRank = $this->getSessionRank($currentSession);

        /** @var EmployeeAttendance[] $openAttendances */
        $openAttendances = EmployeeAttendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->whereNull('check_out_at')
            ->whereNotNull('check_in_at')
            ->orderBy('check_in_at')
            ->get()
            ->filter(function (EmployeeAttendance $attendance) use ($currentSessionRank): bool {
                return $this->getSessionRank($attendance->session) < $currentSessionRank;
            });

        foreach ($openAttendances as $attendance) {
            $checkoutAt = $this->resolveSessionEndAt($attendance);
            $totalHours = $attendance->check_in_at
                ? EmployeeAttendance::calculateTotalHours(
                    $attendance->employee_id,
                    $attendance->date->copy(),
                    $attendance->check_in_at->copy(),
                    $checkoutAt,
                    $attendance->session,
                )
                : null;

            $attendance->update([
                'check_out_at' => $checkoutAt,
                'check_out_ip' => $ip,
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
                'total_hours' => $totalHours,
                'auto_closed' => true,
                'flagged_missing_checkout' => true,
                'flagged_location' => $attendance->flagged_location || ! $locationVerified,
                'verification_status' => ($attendance->flagged_location || ! $locationVerified) ? 'pending' : 'approved',
                'notes' => $this->resolveAutoCloseNote($attendance->session),
            ]);
        }
    }

    protected function resolveSessionEndAt(EmployeeAttendance $attendance): Carbon
    {
        $date = $attendance->date->toDateString();

        return match ($attendance->session) {
            EmployeeAttendance::SESSION_MORNING => Carbon::parse($date.' '.settings('office_morning_end', '12:00')),
            EmployeeAttendance::SESSION_AFTERNOON => Carbon::parse($date.' '.settings('office_afternoon_end', '17:30')),
            default => Carbon::parse($date.' '.settings('office_evening_end', '21:00')),
        };
    }

    protected function resolveAutoCloseNote(string $session): string
    {
        return match ($session) {
            EmployeeAttendance::SESSION_MORNING => 'Hệ thống tự động checkout cuối ca sáng',
            EmployeeAttendance::SESSION_AFTERNOON => 'Hệ thống tự động checkout cuối ca chiều',
            default => 'Hệ thống tự động checkout cuối ca tối',
        };
    }

    protected function getSessionRank(string $session): int
    {
        return match ($session) {
            EmployeeAttendance::SESSION_MORNING => 1,
            EmployeeAttendance::SESSION_AFTERNOON => 2,
            EmployeeAttendance::SESSION_EVENING => 3,
            default => 0,
        };
    }

    /**
     * Gửi Filament Notification tới các user có quyền manage_employee_attendances.
     */
    protected function notifyAdminAboutAutoClose(Employee $employee, EmployeeAttendance $attendance): void
    {
        $admins = User::permission('employee_attendances.manage')->get();
        $superAdmins = User::where('is_super_admin', true)->get();

        $recipients = $admins->merge($superAdmins)->unique('id');

        foreach ($recipients as $admin) {
            Notification::make()
                ->title(trans('packages.employee::employee_attendance.notifications.auto_close_title'))
                ->body(trans('packages.employee::employee_attendance.notifications.auto_close_body', [
                    'name' => $employee->name,
                    'date' => $attendance->date->format('d/m/Y'),
                    'checkout' => $attendance->check_out_at?->format('H:i'),
                ]))
                ->warning()
                ->sendToDatabase($admin);
        }
    }
}
