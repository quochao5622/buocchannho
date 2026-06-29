<?php

namespace Quochao56\Equipment\Filament\Resources;

use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource\Pages\CreateEquipmentCategory;
use Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource\Pages\EditEquipmentCategory;
use Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource\Pages\ListEquipmentCategories;
use Quochao56\Equipment\Models\EquipmentCategory;

class EquipmentCategoryResource extends Resource
{
    protected static ?string $model = EquipmentCategory::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string|\BackedEnum|\Illuminate\Contracts\Support\Htmlable|null
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
        return $schema->components([
            TextInput::make('code')
                ->label(trans('packages.equipment::equipment_category.fields.code'))
                ->maxLength(50)
                ->default(fn () => (new EquipmentCategory())->generateCode()),

            TextInput::make('name')
                ->label(trans('packages.equipment::equipment_category.fields.name'))
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            
            Select::make('parent_id')
                ->label(trans('packages.equipment::equipment_category.fields.parent_id'))
                ->options(fn (?EquipmentCategory $record) => EquipmentCategory::getTreeOptions($record?->id))
                ->default(null)
                ->nullable(),

            Textarea::make('description')
                ->label(trans('packages.equipment::equipment_category.fields.description'))
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
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
                    ->formatStateUsing(fn (EquipmentCategory $record) => str_repeat('— ', $record->getDepth()) . $record->name),
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
                    if (!empty($orderedIds)) {
                        $query->orderByRaw('FIELD(id, ' . implode(',', $orderedIds) . ')');
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

    public static function getPages(): array
    {
        return [
            'index' => ListEquipmentCategories::route('/'),
            'create' => CreateEquipmentCategory::route('/create'),
            'edit' => EditEquipmentCategory::route('/{record}/edit'),
        ];
    }
}