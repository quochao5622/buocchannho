<?php

namespace Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Quochao56\Equipment\Models\EquipmentInventory;

class EquipmentInventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('inventory_code')
                ->label(trans('packages.equipment::equipment_inventory.fields.inventory_code'))
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true)
                ->default(fn() => (new EquipmentInventory)->generateCode()),

            Hidden::make('inspector_id')
                ->default(fn() => Auth::id()),

            DatePicker::make('inventory_date')
                ->label(trans('packages.equipment::equipment_inventory.fields.inventory_date'))
                ->native(false)
                ->default(now())
                ->displayFormat('d/m/Y')
                ->required(),

            Select::make('status')
                ->label(trans('packages.equipment::equipment_inventory.fields.status'))
                ->options(EquipmentInventory::statusOptions())
                ->default('draft')
                ->required(),

            Textarea::make('notes')
                ->label(trans('packages.equipment::equipment_inventory.fields.notes'))
                ->columnSpanFull(),
        ]);
    }
}
