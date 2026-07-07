<?php

namespace Quochao56\Employee\Services;

use Carbon\Carbon;
use Quochao56\Core\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Quochao56\Employee\Helpers\GeoFenceHelper;
use Quochao56\Employee\Models\Employee;
use Quochao56\Employee\Models\EmployeeAttendance;

class AttendanceCheckinService
{
    /**
     * Thực hiện check-in cho nhân viên.
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

            // 2. Xác thực vị trí
            $latitude = $locationData['latitude'] ?? null;
            $longitude = $locationData['longitude'] ?? null;
            $ip = $locationData['ip'] ?? request()->ip();

            // Validate geofence - may throw exception if location invalid and flagged location disabled
            $locationVerified = $this->validateGeofenceLocation($latitude, $longitude, $ip);

            $status = $this->determineCheckInStatus($employee);

            // 3. Tạo bản ghi check-in mới (hoặc cập nhật nếu đã có bản ghi on_leave)
            return EmployeeAttendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => now()->toDateString(),
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
        });
    }

    /**
     * Xác định trạng thái điểm danh (đúng giờ, đi muộn) dựa trên thời gian check-in
     * và đơn xin nghỉ phép nửa buổi (nếu có).
     */
    protected function determineCheckInStatus(Employee $employee): string
    {
        $today = now()->toDateString();
        
        $leaveRequest = \Quochao56\Employee\Models\LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($leaveRequest && $leaveRequest->half_day && $leaveRequest->half_day_session === 'morning') {
            // Xin phép nghỉ sáng -> Giờ bắt đầu làm việc là đầu giờ chiều
            $expectedStartTime = settings('office_afternoon_start', '13:30');
        } else {
            // Bình thường hoặc xin phép nghỉ chiều -> Giờ bắt đầu làm việc là đầu giờ sáng
            $expectedStartTime = settings('office_morning_start', '08:00');
        }

        $allowedLateMinutes = (int) settings('office_allowed_late_minutes', 0);
        $expectedStartCarbon = Carbon::parse($today . ' ' . $expectedStartTime);
        $lateThreshold = $expectedStartCarbon->copy()->addMinutes($allowedLateMinutes);

        if (now()->greaterThan($lateThreshold)) {
            return 'late';
        }

        return 'present';
    }

    /**
     * Thực hiện check-out cho nhân viên.
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
        $totalHours = $checkIn ? round(abs($checkOut->diffInMinutes($checkIn)) / 60, 2) : null;

        $attendance->update([
            'check_out_at' => $checkOut,
            'check_out_ip' => $ip,
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
            'total_hours' => $totalHours,
            'flagged_location' => $attendance->flagged_location || ! $locationVerified,
            'verification_status' => ($attendance->flagged_location || ! $locationVerified)
                ? 'pending'
                : 'approved',
        ]);

        return $attendance->fresh();
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
        $openSession = EmployeeAttendance::where('employee_id', $employee->id)
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
        ]);

        // Gửi thông báo Admin nếu được cấu hình
        if (settings('open_session_notify_admin', true)) {
            $this->notifyAdminAboutAutoClose($employee, $openSession);
        }
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
