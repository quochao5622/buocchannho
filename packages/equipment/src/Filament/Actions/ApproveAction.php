<?php

namespace Quochao56\Equipment\Filament\Actions;

use Filament\Actions\Action;
use Quochao56\Equipment\Models\EquipmentInventory;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Quochao56\Equipment\Models\Equipment;

class ApproveAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'approve';
    }

    public function handle(EquipmentInventory $record): void
    {
        try {
            
            DB::transaction(function () use ($record) {
                $record->loadMissing('details');
    
                foreach ($record->details as $detail) {
                    $equipment = Equipment::query()->find($detail->equipment_id);
                    if (! $equipment) {
                        continue;
                    }
    
                    $equipment->update([
                        'quantity' => (int) $detail->quantity_actual,
                        'status' => (string) $detail->status,
                    ]);
                }
    
                $record->update(['status' => 'approved']);
            });
    
            Notification::make()
                ->title(trans('packages.equipment::equipment.inventory.approve.success'))
                ->success()
                ->send();
        } catch (\Throwable $th) {
            Notification::make()
                ->title(trans('packages.equipment::equipment.inventory.approve.error'))
                ->danger()
                ->send();
            Log::error($th);
        }
        
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('packages.equipment::equipment.inventory.approve.label'));
        $this->color('success');
        $this->requiresConfirmation();
        $this->visible(fn (EquipmentInventory $record) => $record->status === 'completed');

        $this->action(fn (EquipmentInventory $record) => $this->handle($record));
    }
}