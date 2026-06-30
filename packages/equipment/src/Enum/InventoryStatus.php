<?php

namespace Quochao56\Equipment\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InventoryStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Completed = 'completed';
    case Approved = 'approved';

    public function getLabel(): string
    {
        return trans('packages.equipment::equipment_inventory.status.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Completed => 'info',
            self::Approved => 'success',
        };
    }
}
