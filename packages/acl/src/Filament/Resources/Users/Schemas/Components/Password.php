<?php

namespace Quochao56\Acl\Filament\Resources\Users\Schemas\Components;

use Filament\Forms;
use Filament\Forms\Components\TextInput;

class Password
{
    public static function make(): TextInput
    {
        return Forms\Components\TextInput::make('password')
            ->hidden(static fn ($record) => $record)
            ->label(trans('filament-users::user.resource.password'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required(static fn ($record) => ! $record)
            ->rule(\Illuminate\Validation\Rules\Password::default())
            ->dehydrated(static fn ($state) => filled($state))
            ->dehydrateStateUsing(fn ($state) => bcrypt($state))
            ->same('passwordConfirmation')
            ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute'));
    }
}
