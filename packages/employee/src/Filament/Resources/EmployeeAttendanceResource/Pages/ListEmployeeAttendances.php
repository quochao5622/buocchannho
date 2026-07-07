<?php

namespace Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
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
                ->visible(fn () => auth()->user()->hasPermissionTo('employee_attendances.create')),

            // Custom Check-in Action
            Action::make('checkIn')
                ->label(trans('packages.employee::employee_attendance.actions.check_in'))
                ->icon('heroicon-o-play')
                ->color('success')
                ->visible(fn () => $this->canCheckIn())
                ->modalHeading(trans('packages.employee::employee_attendance.actions.check_in'))
                ->modalDescription('Hệ thống sẽ ghi nhận thời gian và vị trí của bạn để thực hiện chấm công.')
                ->modalSubmitActionLabel('Bắt đầu Check-in')
                ->form([
                    Hidden::make('latitude')
                        ->extraAttributes(['id' => 'geo-latitude']),
                    Hidden::make('longitude')
                        ->extraAttributes(['id' => 'geo-longitude']),
                    Placeholder::make('gps_status')
                        ->content(new HtmlString('
                            <div x-data="{
                                lat: null,
                                lng: null,
                                error: null,
                                loading: true,
                                init() {
                                    if (!navigator.geolocation) {
                                        this.error = \'Trình duyệt của bạn không hỗ trợ định vị GPS.\';
                                        this.loading = false;
                                        return;
                                    }
                                    navigator.geolocation.getCurrentPosition(
                                        (position) => {
                                            this.lat = position.coords.latitude;
                                            this.lng = position.coords.longitude;
                                            document.getElementById(\'geo-latitude\').value = this.lat;
                                            document.getElementById(\'geo-latitude\').dispatchEvent(new Event(\'input\'));
                                            document.getElementById(\'geo-longitude\').value = this.lng;
                                            document.getElementById(\'geo-longitude\').dispatchEvent(new Event(\'input\'));
                                            this.loading = false;
                                        },
                                        (err) => {
                                            this.error = \'Không thể lấy vị trí: \' + err.message + \'. Vui lòng cấp quyền định vị cho trình duyệt.\';
                                            this.loading = false;
                                        },
                                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                                    );
                                }
                            }">
                                <template x-if="loading">
                                    <div class="flex items-center space-x-2 text-primary-600">
                                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Đang xác định vị trí GPS của bạn...</span>
                                    </div>
                                </template>
                                <template x-if="lat && lng">
                                    <div class="p-3 bg-success-50 text-success-800 rounded-lg text-sm flex items-center space-x-2">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Định vị vị trí thành công! Sẵn sàng chấm công.</span>
                                    </div>
                                </template>
                                <template x-if="error">
                                    <div class="p-3 bg-danger-50 text-danger-800 rounded-lg text-sm flex items-start space-x-2">
                                        <svg class="h-5 w-5 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <span x-text="error"></span>
                                    </div>
                                </template>
                            </div>
                        ')),
                ])
                ->action(fn (array $data) => $this->handleCheckIn($data)),

            // Custom Check-out Action
            Action::make('checkOut')
                ->label(trans('packages.employee::employee_attendance.actions.check_out'))
                ->icon('heroicon-o-stop')
                ->color('warning')
                ->visible(fn () => $this->canCheckOut())
                ->modalHeading(trans('packages.employee::employee_attendance.actions.check_out'))
                ->modalDescription(function () {
                    $minMinutes = (int) (settings('checkout_minimum_minutes') ?? 0);
                    if ($minMinutes > 0) {
                        $user = auth()->user();
                        $employee = Employee::where('email', $user->email)->first();
                        if ($employee) {
                            $attendance = EmployeeAttendance::where('employee_id', $employee->id)
                                ->whereDate('date', Carbon::today())
                                ->whereNotNull('check_in_at')
                                ->whereNull('check_out_at')
                                ->first();

                            if ($attendance) {
                                $diff = $attendance->check_in_at->diffInMinutes(now());
                                if ($diff < $minMinutes) {
                                    $diffFormatted = round($diff);
                                    if ($diffFormatted < 1) {
                                        return 'Bạn có chắc chắn muốn check-out? Bạn mới check-in chưa đầy 1 phút trước.';
                                    }

                                    return "Bạn có chắc chắn muốn check-out? Bạn vừa check-in {$diffFormatted} phút trước.";
                                }
                            }
                        }
                    }

                    return 'Hệ thống sẽ ghi nhận thời gian và vị trí của bạn để hoàn thành chấm công ngày.';
                })
                ->modalSubmitActionLabel('Bắt đầu Check-out')
                ->form([
                    Hidden::make('latitude')
                        ->extraAttributes(['id' => 'geo-latitude']),
                    Hidden::make('longitude')
                        ->extraAttributes(['id' => 'geo-longitude']),
                    Placeholder::make('gps_status')
                        ->content(new HtmlString('
                            <div x-data="{
                                lat: null,
                                lng: null,
                                error: null,
                                loading: true,
                                init() {
                                    if (!navigator.geolocation) {
                                        this.error = \'Trình duyệt của bạn không hỗ trợ định vị GPS.\';
                                        this.loading = false;
                                        return;
                                    }
                                    navigator.geolocation.getCurrentPosition(
                                        (position) => {
                                            this.lat = position.coords.latitude;
                                            this.lng = position.coords.longitude;
                                            document.getElementById(\'geo-latitude\').value = this.lat;
                                            document.getElementById(\'geo-latitude\').dispatchEvent(new Event(\'input\'));
                                            document.getElementById(\'geo-longitude\').value = this.lng;
                                            document.getElementById(\'geo-longitude\').dispatchEvent(new Event(\'input\'));
                                            this.loading = false;
                                        },
                                        (err) => {
                                            this.error = \'Không thể lấy vị trí: \' + err.message + \'. Vui lòng cấp quyền định vị cho trình duyệt.\';
                                            this.loading = false;
                                        },
                                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                                    );
                                }
                            }">
                                <template x-if="loading">
                                    <div class="flex items-center space-x-2 text-primary-600">
                                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Đang xác định vị trí GPS của bạn...</span>
                                    </div>
                                </template>
                                <template x-if="lat && lng">
                                    <div class="p-3 bg-success-50 text-success-800 rounded-lg text-sm flex items-center space-x-2">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Định vị vị trí thành công! Sẵn sàng chấm công.</span>
                                    </div>
                                </template>
                                <template x-if="error">
                                    <div class="p-3 bg-danger-50 text-danger-800 rounded-lg text-sm flex items-start space-x-2">
                                        <svg class="h-5 w-5 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <span x-text="error"></span>
                                    </div>
                                </template>
                            </div>
                        ')),
                ])
                ->action(fn (array $data) => $this->handleCheckOut($data)),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        $user = auth()->user();

        if ($user->isSuperAdmin() || $user->hasPermissionTo('employee_attendances.view_all')) {
            return $query;
        }

        $employee = Employee::where('email', $user->email)->first();
        if ($employee) {
            return $query->where('employee_id', $employee->id);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function canCheckIn(): bool
    {
        $user = auth()->user();
        $employee = Employee::where('email', $user->email)->first();

        if (! $employee) {
            return false;
        }

        // Check if already checked in today
        $attendance = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->first();

        return ! $attendance;
    }

    protected function canCheckOut(): bool
    {
        $user = auth()->user();
        $employee = Employee::where('email', $user->email)->first();

        if (! $employee) {
            return false;
        }

        // Check if checked in today but not yet checked out
        $attendance = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->first();

        return $attendance && $attendance->check_in_at && ! $attendance->check_out_at;
    }

    protected function handleCheckIn(array $data): void
    {
        $user = auth()->user();
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

        // Calculate Status based on shift time
        $now = Carbon::now();
        $limitHour = Carbon::createFromFormat('H:i', settings('office_morning_end') ?? '12:00');

        $status = 'present'; // Mặc định Đúng giờ

        if ($now->lessThan($limitHour)) {
            // Ca hành chính -> Xét đi muộn
            $workStartStr = settings('office_morning_start') ?? '08:00';
            $graceMinutes = settings('office_allowed_late_minutes') ?? 15;

            $workStartTime = Carbon::createFromFormat('H:i', $workStartStr);
            $lateLimitTime = (clone $workStartTime)->addMinutes($graceMinutes);

            // Chỉ so sánh giờ/phút/giây
            $currentTime = Carbon::createFromFormat('H:i:s', $now->format('H:i:s'));
            $lateLimitCompare = Carbon::createFromFormat('H:i:s', $lateLimitTime->format('H:i:s'));

            if ($currentTime->greaterThan($lateLimitCompare)) {
                $status = 'late';
            }
        }

        // Save record
        EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'date' => Carbon::today(),
            'check_in_at' => $now,
            'status' => $status,
            'check_in_ip' => request()->ip(),
            'check_in_latitude' => $lat,
            'check_in_longitude' => $lng,
        ]);

        $this->notify(
            Notification::make()
                ->title(trans('packages.employee::employee_attendance.actions.check_in_success', ['time' => $now->format('H:i:s')]))
                ->success()
        );
    }

    protected function handleCheckOut(array $data): void
    {
        $user = auth()->user();
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

        $attendance = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->first();

        if (! $attendance) {
            $this->notify(
                Notification::make()
                    ->title(trans('packages.employee::employee_attendance.actions.not_checked_in_yet'))
                    ->danger()
            );

            return;
        }

        $now = Carbon::now();
        $checkInAt = Carbon::parse($attendance->check_in_at);
        $totalHours = round(abs($now->diffInMinutes($checkInAt)) / 60, 2);

        $attendance->update([
            'check_out_at' => $now,
            'check_out_ip' => request()->ip(),
            'check_out_latitude' => $lat,
            'check_out_longitude' => $lng,
            'total_hours' => $totalHours,
        ]);

        $this->notify(
            Notification::make()
                ->title(trans('packages.employee::employee_attendance.actions.check_out_success', [
                    'time' => $now->format('H:i:s'),
                    'hours' => $totalHours,
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
}
