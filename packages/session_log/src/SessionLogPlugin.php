<?php

namespace Quochao56\SessionLog;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource;
use Quochao56\SessionLog\Filament\Resources\DailyLogResource;

class SessionLogPlugin implements Plugin
{
    public function getId(): string
    {
        return 'session-log';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            DailyLogResource::class,
            BehaviorIncidentResource::class,
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
