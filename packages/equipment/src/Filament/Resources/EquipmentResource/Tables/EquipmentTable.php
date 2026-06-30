<?php

namespace Quochao56\Equipment\Filament\Resources\EquipmentResource\Tables;

use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use Quochao56\Core\Support\VietnameseSearch;
use Quochao56\Equipment\Filament\Exports\EquipmentExcelExport;
use Quochao56\Equipment\Models\Equipment;
use Quochao56\Equipment\Models\EquipmentCategory;

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(trans('packages.equipment::equipment.form.image'))
                    ->square()
                    ->size(48)
                    ->defaultImageUrl(fn (): string => 'https://placehold.co/96x96?text=HC')
                    ->openUrlInNewTab(),
                TextColumn::make('equipment_code')
                    ->label(trans('packages.equipment::equipment.form.equipment_code'))
                    ->searchable(query: VietnameseSearch::column('equipment_code'))
                    ->sortable(),
                TextColumn::make('name')
                    ->label(trans('packages.equipment::equipment.form.name'))
                    ->searchable(query: VietnameseSearch::column('name'))
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label(trans('packages.equipment::equipment.form.category'))
                    ->sortable(),
                TextColumn::make('quantity_good')
                    ->label(trans('packages.equipment::equipment.form.quantity_good'))
                    ->badge()
                    ->color('success')
                    ->sortable(),
                TextColumn::make('quantity_broken')
                    ->label(trans('packages.equipment::equipment.form.quantity_broken'))
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                TextColumn::make('quantity_missing')
                    ->label(trans('packages.equipment::equipment.form.quantity_missing'))
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(trans('packages.equipment::equipment.form.quantity'))
                    ->sortable(),
                TextColumn::make('location')
                    ->label(trans('packages.equipment::equipment.form.location'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('packages.equipment::equipment.form.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('equipment_code', 'asc')
            ->filters([
                SelectFilter::make('category_id')
                    ->label(trans('packages.equipment::equipment.form.category'))
                    ->options(fn () => EquipmentCategory::getTreeOptions()),
            ])
            ->headerActions([
                ExportAction::make('export')
                    ->label(trans('packages.equipment::equipment.form.export'))
                    ->exports([
                        EquipmentExcelExport::make('equipments')
                            ->fromTable()
                            ->withWriterType(Excel::XLSX)
                            ->withFilename(fn (): string => 'hoc-cu-'.now()->format('Y-m-d-H-i-s'))
                            ->withColumns([
                                Column::make('image')
                                    ->heading('Image')
                                    ->width(24)
                                    ->formatStateUsing(fn (): ?string => null),
                                Column::make('name')->heading(trans('packages.equipment::equipment.fields.name')),
                                Column::make('unit')->heading(trans('packages.equipment::equipment.fields.unit')),
                                Column::make('category.name')->heading(trans('packages.equipment::equipment.fields.category_id')),
                                Column::make('quantity')->heading(trans('packages.equipment::equipment.fields.quantity')),
                                Column::make('status')
                                    ->heading(trans('packages.equipment::equipment.fields.status'))
                                    ->formatStateUsing(fn (?string $state): string => Equipment::statusOptions()[$state] ?? ($state ?? '-')),
                                Column::make('actual_quantity')->heading(trans('packages.equipment::equipment.fields.actual_quantity')),
                                Column::make('note')->heading(trans('packages.equipment::equipment.fields.note')),
                            ]),
                    ]),
            ])
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
