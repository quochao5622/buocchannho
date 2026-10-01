<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Pages;

use Filament\Resources\Pages\CreateRecord;
use Quochao56\Core\Traits\HasCreateReminder;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;

class CreatePlanning extends CreateRecord
{
    use HasCreateReminder;

    protected static string $resource = PlanningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->submit(null)
                ->action(fn () => $this->create()),
            ...($this->canCreateAnother() ? [$this->getCreateAnotherFormAction()] : []),
            $this->getCancelFormAction(),
        ];
    }
}
