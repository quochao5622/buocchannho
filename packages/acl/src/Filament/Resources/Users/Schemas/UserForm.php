<?php

namespace Quochao56\Acl\Filament\Resources\Users\Schemas;

use Filament\Fields\Field;
use Filament\Schemas\Schema;
use Quochao56\Acl\Filament\Resources\Users\Schemas\Components\Password;
use TomatoPHP\FilamentUsers\Filament\Resources\Users\Schemas\Components;

class UserForm
{
    // Chia sẻ chung $schema với class gốc (được tomatophp/acl register vào)
    protected static array $schema = [];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::getSchema());
    }

    public static function getDefaultComponents(): array
    {
        $components = [];

        if (filament('filament-user')::hasAvatar()) {
            $components[] = Components\Avatar::make();
        }

        $components[] = Components\Name::make();
        $components[] = Components\Email::make();
        $components[] = Password::make();                      // ← bcrypt()
        $components[] = Components\PasswordConfirmation::make();

        return $components;
    }

    private static function getSchema(): array
    {
        return array_merge(static::getDefaultComponents(), static::$schema);
    }

    public static function register(Field|array $component): void
    {
        if (is_array($component)) {
            foreach ($component as $item) {
                if (! $item instanceof Field) {
                    continue;
                }
                static::$schema[] = $item;
            }

            return;
        }

        static::$schema[] = $component;
    }
}
