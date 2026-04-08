<?php

namespace Quochao56\Equipment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentInventoryDetail extends Model
{
    use HasFactory;

    protected $table = 'equipment_inventory_details';

    protected $fillable = [
        'equipment_inventory_id',
        'equipment_id',
        'quantity_expected',
        'quantity_actual',
        'status',
        'notes',
    ];
    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return Equipment::statusOptions();
    }

    /**
     * @return BelongsTo<EquipmentInventory, $this>
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(EquipmentInventory::class, 'equipment_inventory_id');
    }

    /**
     * @return BelongsTo<Equipment, $this>
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}

