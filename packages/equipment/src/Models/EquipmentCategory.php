<?php

namespace Quochao56\Equipment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentCategory extends Model
{
    use HasFactory;

    protected $table = 'equipment_categories';

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    /**
     * @return HasMany<Equipment, $this>
     */
    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class, 'category_id');
    }

    public function setCodeAttribute(?string $value): void
    {
        $value = is_string($value) ? trim($value) : null;

        $this->attributes['code'] = ($value !== null && $value !== '')
            ? $value
            : $this->generateCode();
    }

    public function generateCode(): string
    {
        if (empty($this->attributes['code'])) {
            $latestCategory = static::latest()->first();
            $latestCode = $latestCategory ? (int) str_replace('DM', '', $latestCategory->code) : 0;
            return 'DM' . str_pad($latestCode + 1, 3, '0', STR_PAD_LEFT);
        }
        return $this->attributes['code'];
    }
}

