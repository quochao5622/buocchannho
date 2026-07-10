<?php

namespace Quochao56\SessionLog\Filament\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BehaviorIntensityEnum: string implements HasColor, HasLabel
{
    case Mild = 'mild';
    case Moderate = 'moderate';
    case High = 'high';
    case Severe = 'severe';

    public function getLabel(): string
    {
        return trans('packages.session_log::behavior_incident.intensity.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Mild => 'success',
            self::Moderate => 'info',
            self::High => 'warning',
            self::Severe => 'danger',
        };
    }
}
