<?php

namespace App\Filament\Plugins;

use App\Filament\Pages\CustomManageSettings;
use DamodarBhattarai\FilamentSettings\FilamentSettingsPlugin;
use Filament\Panel;

class CustomFilamentSettingsPlugin extends FilamentSettingsPlugin
{
    public function register(Panel $panel): void
    {
        $panel->pages([
            CustomManageSettings::class,
        ]);
    }
}
