<?php

namespace Quochao56\Equipment\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource\Pages\CreateEquipmentCategory;
use Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource\Pages\EditEquipmentCategory;
use Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource\Pages\ListEquipmentCategories;
use Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource\Schemas\EquipmentCategoryForm;
use Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource\Tables\EquipmentCategoryTable;
use Quochao56\Equipment\Models\EquipmentCategory;

class EquipmentCategoryResource extends Resource
{
    protected static ?string $model = EquipmentCategory::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.equipment::equipment.common.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.equipment::equipment_category.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.equipment::equipment_category.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.equipment::equipment_category.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return EquipmentCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipmentCategoryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEquipmentCategories::route('/'),
            'create' => CreateEquipmentCategory::route('/create'),
            'edit' => EditEquipmentCategory::route('/{record}/edit'),
        ];
    }
}
