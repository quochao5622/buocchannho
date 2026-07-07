<?php

namespace Quochao56\Employee\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Quochao56\Core\Traits\HasNotifications;
use Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource;
use Quochao56\Employee\Models\Employee;
use Quochao56\Employee\Models\EmployeeAttendance;
use Quochao56\Employee\Services\AttendanceCheckinService;

class MobileCheckinWidget extends Widget implements HasActions, HasForms
{
    use HasNotifications;
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'employee::widgets.mobile-checkin-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -10;

    public ?float $latitude = null;

    public ?float $longitude = null;

    public bool $geoReady = false;

    protected function getViewData(): array
    {
        $employee = $this->getEmployee();

        if (! $employee) {
            return [
                'employee' => null,
                'todayRecord' => null,
                'isCheckedIn' => false,
                'isCheckedOut' => false,
                'checkInDiffMinutes' => 0,
                'checkoutMinimumMinutes' => 0,
            ];
        }

        $todayRecord = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->latest()
            ->first();

        $isCheckedIn = $todayRecord && $todayRecord->check_in_at !== null;
        $isCheckedOut = $isCheckedIn && $todayRecord->check_out_at !== null;

        $checkInDiffMinutes = 0;
        if ($isCheckedIn && ! $isCheckedOut) {
            $checkInDiffMinutes = $todayRecord->check_in_at->diffInMinutes(now());
        }

        $minMinutes = (int) (settings('checkout_minimum_minutes') ?? 0);

        return [
            'employee' => $employee,
            'todayRecord' => $todayRecord,
            'isCheckedIn' => $isCheckedIn,
            'isCheckedOut' => $isCheckedOut,
            'checkInDiffMinutes' => $checkInDiffMinutes,
            'checkoutMinimumMinutes' => $minMinutes,
        ];
    }

    public function checkIn(): void
    {
        $employee = $this->getEmployee();

        if (! $employee) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.no_associated_employee'))
                    ->danger()
            );

            return;
        }

        $existing = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->whereNotNull('check_in_at')
            ->first();

        if ($existing) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.already_checked_in'))
                    ->warning()
            );

            return;
        }

        try {
            $service = app(AttendanceCheckinService::class);
            $record = $service->checkIn($employee, [
                'latitude' => $this->latitude ?: null,
                'longitude' => $this->longitude ?: null,
                'ip' => request()->ip(),
            ]);

            $message = trans('packages.employee::employee_attendance.actions.check_in_success', [
                'time' => $record->check_in_at->format('H:i'),
            ]);

            $notification = Notification::make()->title($message)->success();

            if ($record->flagged_location) {
                $centerLat = settings('center_latitude');
                $centerLon = settings('center_longitude');
                $distance = $this->latitude && $this->longitude 
                    ? \Quochao56\Employee\Helpers\GeoFenceHelper::distanceInMeters($this->latitude, $this->longitude, $centerLat, $centerLon)
                    : null;
                $distanceText = $distance !== null ? round($distance) : '?';

                $notification = Notification::make()
                    ->title($message)
                    ->body(trans('packages.employee::employee_attendance.actions.outside_radius', ['distance' => $distanceText]))
                    ->warning();
            }

            $this->notify($notification);
        } catch (\InvalidArgumentException $e) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.check_in_failed'))
                    ->body($e->getMessage())
                    ->danger()
            );

            return;
        }

        $this->dispatch('$refresh');
    }

    public function checkOut(): void
    {
        $employee = $this->getEmployee();

        if (! $employee) {
            return;
        }

        $record = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->latest()
            ->first();

        if (! $record) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.not_checked_in_yet'))
                    ->warning()
            );

            return;
        }

        try {
            $service = app(AttendanceCheckinService::class);
            $record = $service->checkOut($record, [
                'latitude' => $this->latitude ?: null,
                'longitude' => $this->longitude ?: null,
                'ip' => request()->ip(),
            ]);

            $message = trans('packages.employee::employee_attendance.actions.check_out_success', [
                'time' => $record->check_out_at->format('H:i'),
                'hours' => $record->total_hours ?? '—',
            ]);

            $notification = Notification::make()->title($message)->success();

            if ($record->flagged_location) {
                $centerLat = settings('center_latitude');
                $centerLon = settings('center_longitude');
                $distance = $this->latitude && $this->longitude 
                    ? \Quochao56\Employee\Helpers\GeoFenceHelper::distanceInMeters($this->latitude, $this->longitude, $centerLat, $centerLon)
                    : null;
                $distanceText = $distance !== null ? round($distance) : '?';

                $notification = Notification::make()
                    ->title($message)
                    ->body(trans('packages.employee::employee_attendance.actions.outside_radius', ['distance' => $distanceText]))
                    ->warning()
                    ->actions([
                        Action::make('view_approval')
                            ->label(trans('packages.employee::employee_attendance.actions.view_approval'))
                            ->url(EmployeeAttendanceResource::getUrl('index'))
                            ->openUrlInNewTab(),
                    ]);
            }

            $this->notify($notification);
        } catch (\InvalidArgumentException $e) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.check_out_failed'))
                    ->body($e->getMessage())
                    ->danger()
            );

            return;
        }

        $this->dispatch('$refresh');
    }

    public function checkOutAction(): Action
    {
        return Action::make('checkOutAction')
            ->requiresConfirmation()
            ->modalHeading(trans('packages.employee::employee_attendance.actions.check_out'))
            ->modalDescription(function () {
                $employee = $this->getEmployee();
                if (! $employee) {
                    return '';
                }

                $record = EmployeeAttendance::where('employee_id', $employee->id)
                    ->whereDate('date', now()->toDateString())
                    ->whereNotNull('check_in_at')
                    ->whereNull('check_out_at')
                    ->latest()
                    ->first();

                if ($record) {
                    $diff = $record->check_in_at->diffInMinutes(now());
                    $diffFormatted = round($diff);
                    if ($diffFormatted < 1) {
                        return 'Bạn có chắc chắn muốn check-out? Bạn mới check-in chưa đầy 1 phút trước.';
                    }

                    return "Bạn có chắc chắn muốn check-out? Bạn vừa check-in {$diffFormatted} phút trước.";
                }

                return '';
            })
            ->modalSubmitActionLabel('Check-out')
            ->action(fn () => $this->checkOut());
    }

    protected function getEmployee(): ?Employee
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return Employee::where('email', $user->email)->first();
    }
}
