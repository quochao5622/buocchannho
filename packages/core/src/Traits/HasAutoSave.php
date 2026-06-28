<?php

namespace Quochao56\Core\Traits;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

trait HasAutoSave
{
    public function autoSave(): void
    {
        \Illuminate\Support\Facades\Log::info('Autosave triggered on ' . static::class);

        $this->save(shouldRedirect: false, shouldSendSavedNotification: false);

        \Illuminate\Support\Facades\Log::info('Autosave completed on ' . static::class);

        $notification = Notification::make()
            ->title(trans('packages.core::core.messages.autosaved'))
            ->success()
            ->duration(3000); // Tự động ẩn sau 3 giây

        $this->dispatch('notificationSent', notification: $notification->toArray());
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        $schema = parent::form($schema);

        $components = $schema->getComponents();

        // Chèn trực tiếp view chứa wire:poll vào form
        $components[] = \Filament\Schemas\Components\View::make('filament.autosave');

        return $schema->components($components);
    }
}
