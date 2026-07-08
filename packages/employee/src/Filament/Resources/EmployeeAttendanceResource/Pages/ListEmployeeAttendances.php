<?php

namespace Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource\Pages;

use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Quochao56\Core\Traits\HasNotifications;
use Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource;
use Quochao56\Employee\Models\Employee;
use Quochao56\Employee\Models\EmployeeAttendance;

class ListEmployeeAttendances extends ListRecords
{
    use HasNotifications;

    protected static string $resource = EmployeeAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => Auth::user()?->hasPermissionTo('employee_attendances.create')),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        $user = Auth::user();

        if ($user->isSuperAdmin() || $user->hasPermissionTo('employee_attendances.view_all')) {
            return $query;
        }

        $employee = Employee::where('email', $user->email)->first();
        if ($employee) {
            return $query->where('employee_id', $employee->id);
        }

        return $query->whereKey(-1);
    }

    protected function canCheckIn(): bool
    {
        $user = Auth::user();
        $employee = Employee::where('email', $user->email)->first();

        if (! $employee) {
            return false;
        }

        // Check if already checked in today
        /** @var EmployeeAttendance|null $attendance */
        $attendance = EmployeeAttendance::query()->where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->first();

        return ! $attendance;
    }

    protected function canCheckOut(): bool
    {
        $user = Auth::user();
        $employee = Employee::where('email', $user->email)->first();

        if (! $employee) {
            return false;
        }

        // Check if checked in today but not yet checked out
        /** @var EmployeeAttendance|null $attendance */
        $attendance = EmployeeAttendance::query()->where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->first();

        return $attendance && $attendance->check_in_at && ! $attendance->check_out_at;
    }

    protected function handleCheckIn(array $data): void
    {
        $user = Auth::user();
        $employee = Employee::where('email', $user->email)->first();

        if (! $employee) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.no_associated_employee'))
                    ->danger()
            );

            return;
        }

        $lat = $data['latitude'] ?? null;
        $lng = $data['longitude'] ?? null;
        $session = $this->currentSessionKey();

        $existingAttendance = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->where('session', $session)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_at) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.already_checked_in'))
                    ->warning()
            );

            return;
        }

        if (is_null($lat) || is_null($lng)) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.geolocation_required'))
                    ->danger()
            );

            return;
        }

        // GPS Validation
        $centerLat = settings('center_latitude') ?? 10.776889;
        $centerLng = settings('center_longitude') ?? 106.700806;
        $allowedRadius = settings('allowed_radius_meters') ?? 50;

        $distance = $this->getDistance((float) $lat, (float) $lng, (float) $centerLat, (float) $centerLng);

        if ($distance > $allowedRadius) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.outside_radius', ['distance' => round($distance)]))
                    ->danger()
            );

            return;
        }

        // Calculate Status based on shift time
        $now = Carbon::now();
        $workStartStr = $this->resolveExpectedStartTimeForNow($now);
        $graceMinutes = (int) (settings('office_allowed_late_minutes') ?? 15);

        $workStartTime = Carbon::createFromTimeString((string) $workStartStr);
        $lateLimitTime = (clone $workStartTime)->addMinutes($graceMinutes);

        $currentTime = Carbon::createFromFormat('H:i:s', $now->format('H:i:s'));
        $lateLimitCompare = Carbon::createFromFormat('H:i:s', $lateLimitTime->format('H:i:s'));

        $status = $currentTime->greaterThan($lateLimitCompare) ? 'late' : 'present';

        // Save record
        EmployeeAttendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => Carbon::today(),
                'session' => $session,
            ],
            [
                'check_in_at' => $now,
                'status' => $status,
                'check_in_ip' => request()->ip(),
                'check_in_latitude' => $lat,
                'check_in_longitude' => $lng,
            ]
        );

        $this->notify(
            Notification::make()
                ->title(trans('packages.employee::employee_attendance.actions.check_in_success', ['time' => $now->format('H:i:s')]))
                ->success()
        );
    }

    protected function handleCheckOut(array $data): void
    {
        $user = Auth::user();
        $employee = Employee::where('email', $user->email)->first();

        if (! $employee) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.no_associated_employee'))
                    ->danger()
            );

            return;
        }

        $lat = $data['latitude'] ?? null;
        $lng = $data['longitude'] ?? null;
        if (is_null($lat) || is_null($lng)) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.geolocation_required'))
                    ->danger()
            );

            return;
        }

        // GPS Validation
        $centerLat = settings('center_latitude') ?? 10.776889;
        $centerLng = settings('center_longitude') ?? 106.700806;
        $allowedRadius = settings('allowed_radius_meters') ?? 50;

        $distance = $this->getDistance((float) $lat, (float) $lng, (float) $centerLat, (float) $centerLng);

        if ($distance > $allowedRadius) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.outside_radius', ['distance' => round($distance)]))
                    ->danger()
            );

            return;
        }

        /** @var EmployeeAttendance|null $attendance */
        $attendance = $this->findOpenAttendanceForCheckout($employee);

        if (! $attendance) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.not_checked_in_yet'))
                    ->danger()
            );

            return;
        }

        if ($attendance->check_out_at) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.already_checked_out'))
                    ->warning()
            );

            return;
        }

        $service = app(AttendanceCheckinService::class);
        $record = $service->checkOut($attendance, [
            'latitude' => $lat,
            'longitude' => $lng,
            'ip' => request()->ip(),
        ]);

        $this->notify(
            Notification::make()
                ->title(trans('packages.employee::employee_attendance.actions.check_out_success', [
                    'time' => $record->check_out_at->format('H:i:s'),
                    'hours' => $record->total_hours ?? '—',
                ]))
                ->success()
        );
    }

    private function getDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function resolveExpectedStartTimeForNow(Carbon $now): string
    {
        $morningStart = settings('office_morning_start') ?? '08:00';
        $morningEnd = settings('office_morning_end') ?? '12:00';
        $afternoonStart = settings('office_afternoon_start') ?? '13:30';
        $afternoonEnd = settings('office_afternoon_end') ?? '17:30';
        $eveningStart = settings('office_evening_start') ?? '18:00';

        $currentTime = Carbon::createFromFormat('H:i:s', $now->format('H:i:s'));
        $morningEndTime = Carbon::createFromTimeString((string) $morningEnd);
        $afternoonEndTime = Carbon::createFromTimeString((string) $afternoonEnd);

        if ($currentTime->lte($morningEndTime)) {
            return $morningStart;
        }

        if ($currentTime->lte($afternoonEndTime)) {
            return $afternoonStart;
        }

        return $eveningStart;
    }

    private function currentSessionKey(): string
    {
        return EmployeeAttendance::resolveSessionForDateTime(now());
    }

    private function currentSessionLabel(): string
    {
        return trans('packages.employee::employee_attendance.sessions.'.$this->currentSessionKey());
    }

    private function findOpenAttendanceForCheckout(Employee $employee): ?EmployeeAttendance
    {
        $currentSession = $this->currentSessionKey();

        /** @var EmployeeAttendance|null $attendance */
        $attendance = EmployeeAttendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->where('session', $currentSession)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->latest('check_in_at')
            ->first();

        if ($attendance) {
            return $attendance;
        }

        /** @var EmployeeAttendance|null $fallbackAttendance */
        $fallbackAttendance = EmployeeAttendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->latest('check_in_at')
            ->first();

        return $fallbackAttendance;
    }
}
