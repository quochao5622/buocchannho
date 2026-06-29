<?php

namespace Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Tables;

use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Quochao56\Core\Support\VietnameseSearch;
use Quochao56\Equipment\Filament\Actions\ApproveAction;
use Quochao56\Equipment\Models\EquipmentInventory;

class EquipmentInventoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inventory_code')
                    ->label(trans('packages.equipment::equipment_inventory.fields.inventory_code'))
                    ->searchable(query: VietnameseSearch::column('inventory_code'))
                    ->sortable(),
                TextColumn::make('inventory_date')
                    ->label(trans('packages.equipment::equipment_inventory.fields.inventory_date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('inspector.name')
                    ->label(trans('packages.equipment::equipment_inventory.fields.inspector'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(trans('packages.equipment::equipment_inventory.fields.status'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'draft' => 'gray',
                        'completed' => 'info',
                        'approved' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => EquipmentInventory::statusOptions()[$state] ?? ($state ?? '-')),
                TextColumn::make('updated_at')
                    ->label(trans('packages.equipment::equipment_inventory.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('inventory_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('packages.equipment::equipment_inventory.fields.status'))
                    ->options(EquipmentInventory::statusOptions()),
            ])
            ->actions([
                EditAction::make(),
                ApproveAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                ActionsBulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
