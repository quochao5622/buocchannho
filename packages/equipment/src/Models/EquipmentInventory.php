<?php

namespace Quochao56\Equipment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Core\Models\User;
use Quochao56\Equipment\Enum\InventoryStatus;

class EquipmentInventory extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'equipment_inventories';

    protected $fillable = [
        'inventory_code',
        'inspector_id',
        'inventory_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'inventory_date' => 'date',
        'status' => InventoryStatus::class,
    ];

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            InventoryStatus::Draft->value => InventoryStatus::Draft->getLabel(),
            InventoryStatus::Completed->value => InventoryStatus::Completed->getLabel(),
            InventoryStatus::Approved->value => InventoryStatus::Approved->getLabel(),
        ];
    }

    public function setInventoryCodeAttribute(?string $value): void
    {
        $value = is_string($value) ? trim($value) : null;

        $this->attributes['inventory_code'] = ($value !== null && $value !== '')
            ? $value
            : $this->generateCode();
    }

    public function generateCode(): string
    {
        if (empty($this->attributes['inventory_code'])) {
            $latestInventory = static::latest()->first();
            $latestCode = $latestInventory ? (int) str_replace('INV', '', $latestInventory->inventory_code) : 0;

            return 'INV'.str_pad($latestCode + 1, 10, '0', STR_PAD_LEFT);
        }

        return $this->attributes['inventory_code'];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * @return HasMany<EquipmentInventoryDetail, $this>
     */
    public function details(): HasMany
    {
        return $this->hasMany(EquipmentInventoryDetail::class, 'equipment_inventory_id');
    }

    /**
     * Approve an inventory sheet and update equipment stock atomically.
     */
    public function approve(): void
    {
        DB::transaction(function () {
            $this->refresh();

            if ($this->status !== InventoryStatus::Completed) {
                throw new \RuntimeException('Only completed inventories can be approved.');
            }

            $this->loadMissing('details');

            foreach ($this->details as $detail) {
                $equipment = Equipment::query()->find($detail->equipment_id);
                if (! $equipment) {
                    continue;
                }

                $equipment->update([
                    'quantity_good' => (int) $detail->quantity_actual_good,
                    'quantity_broken' => (int) $detail->quantity_actual_broken,
                    'quantity_missing' => (int) $detail->quantity_actual_missing,
                ]);
            }

            $this->forceFill(['status' => InventoryStatus::Approved])->save();
        });
    }
}
