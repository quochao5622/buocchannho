<?php

namespace Quochao56\Scheduler\Filament\Resources\ScheduleResource\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource;

class ViewSchedule extends ViewRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => auth()->user()?->can('update', $this->getRecord()) || auth()->user()?->hasPermissionTo('schedules.edit')),
        ];
    }
}
