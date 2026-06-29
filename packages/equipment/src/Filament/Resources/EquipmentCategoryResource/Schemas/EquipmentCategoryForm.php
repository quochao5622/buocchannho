<?php

namespace Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Quochao56\Equipment\Models\EquipmentCategory;

class EquipmentCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label(trans('packages.equipment::equipment_category.fields.code'))
                ->maxLength(50)
                ->default(fn () => (new EquipmentCategory)->generateCode()),

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
}
