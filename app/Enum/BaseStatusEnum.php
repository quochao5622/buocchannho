<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BaseStatusEnum: string implements HasColor, HasLabel
{
    case Active    = 'active';
    case Inactive  = 'inactive';
    case Published = 'published';
    case Draft     = 'draft';
    case Pending   = 'pending';

    public function getLabel(): string
    {
        return trans('status.' . $this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active, self::Published => 'success',
            self::Inactive                => 'danger',
            self::Pending                 => 'warning',
            self::Draft                   => 'gray',
        };
    }
}