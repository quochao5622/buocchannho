<?php

namespace Quochao56\Student;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Quochao56\Student\Filament\Resources\StudentResource;

class StudentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'student';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            StudentResource::class,
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
