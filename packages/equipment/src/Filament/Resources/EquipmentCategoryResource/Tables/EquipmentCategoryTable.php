<?php

namespace Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource\Tables;

use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Quochao56\Equipment\Models\EquipmentCategory;

class EquipmentCategoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(trans('packages.equipment::equipment_category.table.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(trans('packages.equipment::equipment_category.table.name'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (EquipmentCategory $record) => str_repeat('— ', $record->getDepth()).$record->name),
                TextColumn::make('parent.name')
                    ->label(trans('packages.equipment::equipment_category.fields.parent_id'))
                    ->sortable(),
                TextColumn::make('equipments_count')
                    ->counts('equipments')
                    ->label(trans('packages.equipment::equipment_category.table.equipments_count'))
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(trans('packages.equipment::equipment_category.table.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->modifyQueryUsing(function ($query) {
                if (empty($query->getQuery()->orders)) {
                    $orderedIds = EquipmentCategory::getTreeIds();
                    if (! empty($orderedIds)) {
                        $query->orderByRaw('FIELD(id, '.implode(',', $orderedIds).')');
                    }
                }

                return $query;
            })
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                ActionsBulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
