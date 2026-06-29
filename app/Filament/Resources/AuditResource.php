<?php

namespace App\Filament\Resources;

use Tapp\FilamentAuditing\Filament\Resources\Audits\AuditResource as BaseAuditResource;

class AuditResource extends BaseAuditResource
{
    protected static string|\UnitEnum|null $navigationGroup = 'Hệ thống';

    public static function getNavigationGroup(): ?string
    {
        return 'Hệ thống';
    }

    public static function getNavigationLabel(): string
    {
        return 'Nhật ký kiểm toán';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Nhật ký kiểm toán';
    }

    public static function getModelLabel(): string
    {
        return 'Nhật ký kiểm toán';
    }
}
