<?php

namespace Quochao56\Equipment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';

    protected $fillable = [
        'equipment_code',
        'name',
        'category_id',
        'image',
        'quantity',
        'status',
        'location',
        'unit',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'good' => 'Tốt',
            'broken' => 'Hỏng',
            'missing' => 'Mất tích',
        ];
    }
    public function setEquipmentCodeAttribute(string $value): void
    {
        $this->attributes['equipment_code'] = $this->generateCode();
    }
    public function generateCode(): string
    {
        if (empty($this->attributes['equipment_code'])) {
            $latestEquipment = static::latest()->first();
            $latestCode = $latestEquipment ? (int) str_replace('HC', '', $latestEquipment->equipment_code) : 0;
            return 'HC' . str_pad($latestCode + 1, 5, '0', STR_PAD_LEFT);
        }
        return $this->attributes['equipment_code'];
    }

    /**
     * @return BelongsTo<EquipmentCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    /**
     * @return HasMany<EquipmentInventoryDetail, $this>
     */
    public function inventoryDetails(): HasMany
    {
        return $this->hasMany(EquipmentInventoryDetail::class, 'equipment_id');
    }
}
