<?php

namespace Quochao56\SessionLog\Filament\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DailyLogEmotionEnum: string implements HasColor, HasLabel
{
    case Happy = 'happy';
    case Normal = 'normal';
    case Irritable = 'irritable';
    case Hyperactive = 'hyperactive';

    public function getLabel(): string
    {
        return trans('packages.session_log::daily_log.emotion.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Happy => 'success',
            self::Normal => 'info',
            self::Irritable => 'danger',
            self::Hyperactive => 'warning',
        };
    }
}
