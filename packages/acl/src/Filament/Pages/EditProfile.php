<?php

namespace Quochao56\Acl\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public static bool $isSimple = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(trans('acl::profile.sections.personal_info'))
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                    ]),

                Section::make(trans('acl::profile.sections.change_password'))
                    ->description(trans('acl::profile.descriptions.leave_empty_to_keep'))
                    ->schema([
                        $this->getCurrentPasswordFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
            ]);
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::auth/pages/edit-profile.form.password_confirmation.label'))
            ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.password_confirmation.validation_attribute'))
            ->password()
            ->autocomplete('new-password')
            ->revealable(filament()->arePasswordsRevealable())
            ->dehydrated(false);
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label(__('filament-panels::auth/pages/edit-profile.form.current_password.label'))
            ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.current_password.validation_attribute'))
            ->password()
            ->autocomplete('current-password')
            ->currentPassword(guard: Filament::getAuthGuard())
            ->revealable(filament()->arePasswordsRevealable())
            ->dehydrated(false);
    }

    protected function afterSave(): void
    {
        $this->data['currentPassword'] = null;
    }
}
