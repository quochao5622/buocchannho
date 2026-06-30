<?php

namespace Quochao56\Equipment\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EquipmentStatus: string implements HasColor, HasLabel
{
    case Good = 'good';
    case Broken = 'broken';
    case Missing = 'missing';

    public function getLabel(): string
    {
        return trans('packages.equipment::equipment.status.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Good => 'success',
            self::Broken => 'danger',
            self::Missing => 'warning',
        };
    }
}
