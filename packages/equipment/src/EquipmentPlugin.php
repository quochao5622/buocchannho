<?php

namespace Quochao56\Equipment;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource;
use Quochao56\Equipment\Filament\Resources\EquipmentResource;

class EquipmentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'equipment';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            EquipmentCategoryResource::class,
            EquipmentResource::class,
            EquipmentInventoryResource::class,
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

