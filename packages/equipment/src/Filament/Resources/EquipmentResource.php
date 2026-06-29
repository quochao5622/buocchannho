<?php

namespace Quochao56\Equipment\Filament\Resources;

use Illuminate\Contracts\Support\Htmlable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Quochao56\Equipment\Filament\Resources\EquipmentResource\Pages\CreateEquipment;
use Quochao56\Equipment\Filament\Resources\EquipmentResource\Pages\EditEquipment;
use Quochao56\Equipment\Filament\Resources\EquipmentResource\Pages\ListEquipments;
use Quochao56\Equipment\Models\Equipment;
use Quochao56\Equipment\Filament\Resources\EquipmentResource\Schemas\EquipmentForm;
use Quochao56\Equipment\Filament\Resources\EquipmentResource\Tables\EquipmentTable;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-archive-box';
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.equipment::equipment.common.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.equipment::equipment.common.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.equipment::equipment.common.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.equipment::equipment.common.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return EquipmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipmentTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEquipments::route('/'),
            'create' => CreateEquipment::route('/create'),
            'edit' => EditEquipment::route('/{record}/edit'),
        ];
    }
}
