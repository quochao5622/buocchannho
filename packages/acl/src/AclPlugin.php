<?php

namespace Quochao56\Acl;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Quochao56\Acl\Filament\Pages\EditProfile;
use Quochao56\Acl\Filament\Resources\RoleResource;

class AclPlugin implements Plugin
{
    public function getId(): string
    {
        return 'acl';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                RoleResource::class,
            ])
            ->profile(EditProfile::class, isSimple: false);
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
