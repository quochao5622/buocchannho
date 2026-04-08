<?php

namespace Quochao56\Equipment\Filament\Actions;

use Filament\Actions\Action;
use Quochao56\Equipment\Models\EquipmentInventory;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
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
                ->title('Đã duyệt phiếu và cập nhật tồn kho.')
                ->success()
                ->send();
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Lỗi khi duyệt phiếu.')
                ->danger()
                ->send();
            Log::error($th);
        }
        
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Duyệt phiếu');
        $this->color('success');
        $this->requiresConfirmation();
        $this->visible(fn (EquipmentInventory $record) => $record->status === 'completed');

        $this->action(fn (EquipmentInventory $record) => $this->handle($record));
    }
}