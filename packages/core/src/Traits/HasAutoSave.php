<?php

namespace Quochao56\Core\Traits;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\View;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

trait HasAutoSave
{
    public function autoSave(): void
    {
        $this->save(shouldRedirect: false, shouldSendSavedNotification: false);

        $notification = Notification::make()
            ->title(trans('packages.core::core.messages.autosaved'))
            ->success()
            ->duration(3000); // Tự động ẩn sau 3 giây

        $this->dispatch('notificationSent', notification: $notification->toArray());
    }

    public function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);

        $components = $schema->getComponents();

        // Chèn trực tiếp view chứa wire:poll vào form
        $components[] = View::make('filament.autosave');

        return $schema->components($components);
    }
}
