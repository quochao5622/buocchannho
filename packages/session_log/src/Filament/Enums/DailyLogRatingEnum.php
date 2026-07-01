<?php

namespace Quochao56\SessionLog\Filament\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DailyLogRatingEnum: string implements HasColor, HasLabel
{
    case Good = 'good';
    case Normal = 'normal';
    case Poor = 'poor';

    public function getLabel(): string
    {
        return trans('packages.session_log::daily_log.rating.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Good => 'success',
            self::Normal => 'info',
            self::Poor => 'danger',
        };
    }
}
