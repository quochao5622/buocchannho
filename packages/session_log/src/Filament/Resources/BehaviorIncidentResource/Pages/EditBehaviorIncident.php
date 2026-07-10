<?php

namespace Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource;

class EditBehaviorIncident extends EditRecord
{
    protected static string $resource = BehaviorIncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(trans('packages.session_log::behavior_incident.actions.delete.heading'))
                ->modalDescription(trans('packages.session_log::behavior_incident.actions.delete.description'))
                ->modalSubmitActionLabel(trans('packages.session_log::behavior_incident.actions.delete.submit'))
                ->modalCancelActionLabel(trans('packages.session_log::behavior_incident.actions.delete.cancel')),
        ];
    }
}
