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
        'parent_id',
        'description',
    ];

    public function parent()
    {
        return $this->belongsTo(EquipmentCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(EquipmentCategory::class, 'parent_id');
    }

    private static ?array $allCategoriesMap = null;

    public function getDepth(): int
    {
        if (self::$allCategoriesMap === null) {
            self::$allCategoriesMap = static::pluck('parent_id', 'id')->toArray();
        }

        $depth = 0;
        $parentId = $this->parent_id;
        while ($parentId && array_key_exists($parentId, self::$allCategoriesMap)) {
            $depth++;
            $parentId = self::$allCategoriesMap[$parentId];
        }

        return $depth;
    }

    public static function getTreeOptions(?int $exceptId = null): array
    {
        $categories = static::with('children')->whereNull('parent_id')->get();
        $options = [];

        $traverse = function ($categories, $prefix = '') use (&$traverse, &$options, $exceptId) {
            foreach ($categories as $category) {
                if ($exceptId && $category->id === $exceptId) {
                    continue;
                }
                $options[$category->id] = $prefix.$category->name;
                $traverse($category->children, $prefix.'— ');
            }
        };

        $traverse($categories);

        return $options;
    }

    public static function getTreeIds(): array
    {
        $categories = static::with('children')->whereNull('parent_id')->get();
        $ids = [];

        $traverse = function ($categories) use (&$traverse, &$ids) {
            foreach ($categories as $category) {
                $ids[] = $category->id;
                $traverse($category->children);
            }
        };

        $traverse($categories);

        return $ids;
    }

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

            return 'DM'.str_pad($latestCode + 1, 3, '0', STR_PAD_LEFT);
        }

        return $this->attributes['code'];
    }
}
