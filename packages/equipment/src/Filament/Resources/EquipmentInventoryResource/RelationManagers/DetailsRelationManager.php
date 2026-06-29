<?php

namespace Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Quochao56\Equipment\Models\EquipmentInventoryDetail;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('equipment.image')
                    ->label(trans('packages.equipment::equipment_inventory_detail.fields.equipment_image'))
                    ->size(50)
                    ->extraImgAttributes([
                        'loading' => 'lazy',
                        'decoding' => 'async',
                    ]),

                TextColumn::make('equipment.equipment_code')
                    ->label(trans('packages.equipment::equipment_inventory_detail.fields.equipment_code'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('equipment.name')
                    ->label(trans('packages.equipment::equipment_inventory_detail.fields.equipment'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('equipment.location')
                    ->label(trans('packages.equipment::equipment_inventory_detail.fields.location'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('quantity_expected')
                    ->label(trans('packages.equipment::equipment_inventory_detail.fields.quantity_expected'))
                    ->sortable()
                    ->width('15px'),

                TextInputColumn::make('quantity_actual')
                    ->label(trans('packages.equipment::equipment_inventory_detail.fields.quantity_actual'))
                    ->type('number')
                    ->rules(['required', 'numeric', 'min:0'])
                    ->width('15px'),

                SelectColumn::make('status')
                    ->label(trans('packages.equipment::equipment_inventory_detail.fields.status'))
                    ->options(EquipmentInventoryDetail::statusOptions())
                    ->rules(['required'])
                    ->width('120px'),

                TextInputColumn::make('notes')
                    ->label(trans('packages.equipment::equipment_inventory_detail.fields.notes'))
                    ->toggleable(),
            ])
            ->defaultSort('equipment.name')
            ->contentGrid([
                'default' => 1,
                'md' => null,
            ]);
    }
}
