<?php

namespace Quochao56\Acl\Filament\Resources\Users\Schemas;

use Filament\Fields\Field;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Quochao56\Acl\Filament\Resources\Users\Schemas\Components\Password;
use TomatoPHP\FilamentUsers\Filament\Resources\Users\Schemas\Components;
use TomatoPHP\FilamentUsers\Filament\Resources\Users\Schemas\Components\Roles as UserRolesField;

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

        $components[] = UserRolesField::make();
        $components[] = Checkbox::make('is_super_admin')
            ->label(trans('acl::user.fields.super_admin'));
        $components[] = Toggle::make('is_active')
            ->label(trans('acl::user.fields.is_active'))
            ->default(true);

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
