<?php

namespace Quochao56\SessionLog\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Quochao56\SessionLog\Filament\Resources\DailyLogResource\Pages\CreateDailyLog;
use Quochao56\SessionLog\Filament\Resources\DailyLogResource\Pages\EditDailyLog;
use Quochao56\SessionLog\Filament\Resources\DailyLogResource\Pages\ListDailyLogs;
use Quochao56\SessionLog\Filament\Resources\DailyLogResource\Schemas\DailyLogForm;
use Quochao56\SessionLog\Filament\Resources\DailyLogResource\Tables\DailyLogTable;
use Quochao56\SessionLog\Models\DailyLog;

class DailyLogResource extends Resource
{
    protected static ?string $model = DailyLog::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-book-open';
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.session_log::daily_log.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.session_log::daily_log.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.session_log::daily_log.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.session_log::daily_log.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return DailyLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyLogTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDailyLogs::route('/'),
            'create' => CreateDailyLog::route('/create'),
            'edit' => EditDailyLog::route('/{record}/edit'),
        ];
    }
}
