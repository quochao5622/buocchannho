<?php

namespace Quochao56\Equipment\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Quochao56\Equipment\Enum\InventoryStatus;
use Quochao56\Equipment\Models\EquipmentInventory;

class ApproveAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'approve';
    }

    public function handle(EquipmentInventory $record): void
    {
        try {
            $record->approve();

            Notification::make()
                ->title(trans('packages.equipment::equipment_inventory.approve.success'))
                ->success()
                ->send();
        } catch (\Throwable $th) {
            Notification::make()
                ->title(trans('packages.equipment::equipment_inventory.approve.error'))
                ->danger()
                ->send();
            Log::error($th);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('packages.equipment::equipment_inventory.approve.label'));
        $this->color('success');
        $this->requiresConfirmation();
        $this->visible(fn (EquipmentInventory $record) => $record->status === InventoryStatus::Completed && auth()->user()->can('equipment_inventories.approve'));

        $this->action(fn (EquipmentInventory $record) => $this->handle($record));
    }
}
