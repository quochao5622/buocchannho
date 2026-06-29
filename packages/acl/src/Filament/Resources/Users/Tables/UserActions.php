<?php

namespace Quochao56\Acl\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Quochao56\Acl\Filament\Resources\Users\Tables\Actions\ChangePassword;
use TomatoPHP\FilamentUsers\Filament\Resources\Users\Tables\Actions;

class UserActions
{
    protected static array $actions = [];

    public static function make(): array
    {
        return static::getActions();
    }

    private static function getDefaultActions(): array
    {
        $actions = [
            Actions\ViewAction::make(),
            Actions\EditAction::make(),
            ChangePassword::make(),          // ← bcrypt()
            Actions\DeleteAction::make(),
        ];

        if (config('filament-users.impersonate.enabled')) {
            $actions[] = Actions\ImpersonateAction::make();
        }

        return $actions;
    }

    private static function getActions(): array
    {
        return array_merge(static::getDefaultActions(), static::$actions);
    }

    public static function register(Action|array $action): void
    {
        if (is_array($action)) {
            foreach ($action as $item) {
                if (! $item instanceof Action) {
                    continue;
                }
                static::$actions[] = $item;
            }

            return;
        }

        static::$actions[] = $action;
    }
}
