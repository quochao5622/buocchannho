<?php

namespace Quochao56\Employee;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource;
use Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource;
use Quochao56\Employee\Filament\Resources\EmployeeResource;
use Quochao56\Employee\Filament\Resources\LeaveRequestResource;
use Quochao56\Employee\Filament\Widgets\MobileCheckinWidget;

class EmployeePlugin implements Plugin
{
    public function getId(): string
    {
        return 'employee';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                EmployeeResource::class,
                EmployeeAttendanceResource::class,
                LeaveRequestResource::class,
                AttendanceCorrectionRequestResource::class,
            ])
            ->widgets([
                MobileCheckinWidget::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
