<?php

namespace Quochao56\Equipment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class EquipmentInventoryDetail extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'equipment_inventory_details';

    protected $fillable = [
        'equipment_inventory_id',
        'equipment_id',
        'quantity_expected_good',
        'quantity_actual_good',
        'quantity_expected_broken',
        'quantity_actual_broken',
        'quantity_expected_missing',
        'quantity_actual_missing',
        'notes',
    ];

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
