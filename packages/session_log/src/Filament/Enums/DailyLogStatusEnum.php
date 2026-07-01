<?php

namespace Quochao56\SessionLog\Filament\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DailyLogStatusEnum: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Completed = 'completed';

    public function getLabel(): string
    {
        return trans('packages.session_log::daily_log.status.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Completed => 'success',
        };
    }
}
