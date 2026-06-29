<?php

namespace Quochao56\Equipment\Filament\Resources;

use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Excel;
use Quochao56\Equipment\Filament\Exports\EquipmentExcelExport;
use Quochao56\Equipment\Filament\Resources\EquipmentResource\Pages\CreateEquipment;
use Quochao56\Equipment\Filament\Resources\EquipmentResource\Pages\EditEquipment;
use Quochao56\Equipment\Filament\Resources\EquipmentResource\Pages\ListEquipments;
use Quochao56\Equipment\Models\Equipment;
use Quochao56\Equipment\Models\EquipmentCategory;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string|\BackedEnum|\Illuminate\Contracts\Support\Htmlable|null
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
        return $schema->components([
            TextInput::make('equipment_code')
                ->label(trans('packages.equipment::equipment.form.equipment_code'))
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true)
                ->default(fn () => (new Equipment())->generateCode()),

            TextInput::make('name')
                ->label(trans('packages.equipment::equipment.form.name'))
                ->required()
                ->maxLength(255),

            Select::make('category_id')
                ->label(trans('packages.equipment::equipment.form.category'))
                ->required()
                ->options(fn () => EquipmentCategory::getTreeOptions()),

            FileUpload::make('image')
                ->label(trans('packages.equipment::equipment.form.image'))
                ->image()
                ->directory('equipments')
                ->imageEditor(),

            TextInput::make('quantity')
                ->label(trans('packages.equipment::equipment.form.quantity'))
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->required(),

            Select::make('status')
                ->label(trans('packages.equipment::equipment.form.status'))
                ->options(Equipment::statusOptions())
                ->default('good')
                ->required(),

            TextInput::make('location')
                ->label(trans('packages.equipment::equipment.form.location'))
                ->maxLength(255),

            TextInput::make('unit')
                ->label(trans('packages.equipment::equipment.form.unit'))
                ->required()
                ->maxLength(50)
                ->default('cái'),

            Textarea::make('note')
                ->label(trans('packages.equipment::equipment.form.note'))
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(trans('packages.equipment::equipment.form.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label(trans('packages.equipment::equipment.form.category'))
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(trans('packages.equipment::equipment.form.quantity'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(trans('packages.equipment::equipment.form.status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Equipment::statusOptions()[$state] ?? ($state ?? '-')),
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
                SelectFilter::make('status')
                    ->label(trans('packages.equipment::equipment.form.status'))
                    ->options(Equipment::statusOptions()),
            ])
            ->headerActions([
                ExportAction::make('export')
                    ->label(trans('packages.equipment::equipment.form.export'))
                    ->exports([
                        EquipmentExcelExport::make('equipments')
                            ->fromTable()
                            ->withWriterType(Excel::XLSX)
                            ->withFilename(fn (): string => 'hoc-cu-' . now()->format('Y-m-d-H-i-s'))
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

    public static function getPages(): array
    {
        return [
            'index' => ListEquipments::route('/'),
            'create' => CreateEquipment::route('/create'),
            'edit' => EditEquipment::route('/{record}/edit'),
        ];
    }
}