<?php

namespace Quochao56\Equipment\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Pages\CreateEquipmentInventory;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Pages\EditEquipmentInventory;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Pages\ListEquipmentInventories;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\RelationManagers\DetailsRelationManager;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Schemas\EquipmentInventoryForm;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Tables\EquipmentInventoryTable;
use Quochao56\Equipment\Models\EquipmentInventory;

class EquipmentInventoryResource extends Resource
{
    protected static ?string $model = EquipmentInventory::class;

    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-clipboard-document-check';
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.equipment::equipment.common.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.equipment::equipment_inventory.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.equipment::equipment_inventory.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.equipment::equipment_inventory.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return EquipmentInventoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipmentInventoryTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEquipmentInventories::route('/'),
            'create' => CreateEquipmentInventory::route('/create'),
            'edit' => EditEquipmentInventory::route('/{record}/edit'),
        ];
    }
}
