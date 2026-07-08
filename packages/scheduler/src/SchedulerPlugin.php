<?php

namespace Quochao56\Scheduler;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Quochao56\Scheduler\Filament\Pages\CalendarOverview;
use Quochao56\Scheduler\Filament\Pages\DailyOperationPage;
use Quochao56\Scheduler\Filament\Resources\ClassroomResource;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource;

class SchedulerPlugin implements Plugin
{
    public function getId(): string
    {
        return 'scheduler';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                ScheduleResource::class,
                ClassroomResource::class,
            ])
            ->pages([
                CalendarOverview::class,
                DailyOperationPage::class,
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
}
