<?php

namespace Quochao56\SessionLog\Filament\Resources\DailyLogResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Quochao56\SessionLog\Filament\Resources\DailyLogResource;

class EditDailyLog extends EditRecord
{
    protected static string $resource = DailyLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(trans('packages.session_log::daily_log.actions.delete.heading'))
                ->modalDescription(trans('packages.session_log::daily_log.actions.delete.description'))
                ->modalSubmitActionLabel(trans('packages.session_log::daily_log.actions.delete.submit'))
                ->modalCancelActionLabel(trans('packages.session_log::daily_log.actions.delete.cancel')),
        ];
    }
}
