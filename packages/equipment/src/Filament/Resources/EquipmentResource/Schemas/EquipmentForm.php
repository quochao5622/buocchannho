<?php

namespace Quochao56\Equipment\Filament\Resources\EquipmentResource\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Quochao56\Equipment\Models\Equipment;
use Quochao56\Equipment\Models\EquipmentCategory;

class EquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('equipment_code')
                ->label(trans('packages.equipment::equipment.form.equipment_code'))
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true)
                ->default(fn () => (new Equipment)->generateCode()),

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

            TextInput::make('quantity_good')
                ->label(trans('packages.equipment::equipment.form.quantity_good'))
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->required(),

            TextInput::make('quantity_broken')
                ->label(trans('packages.equipment::equipment.form.quantity_broken'))
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->required(),

            TextInput::make('quantity_missing')
                ->label(trans('packages.equipment::equipment.form.quantity_missing'))
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->required(),

            TextInput::make('quantity')
                ->label(trans('packages.equipment::equipment.form.quantity'))
                ->numeric()
                ->disabled()
                ->dehydrated(false)
                ->placeholder('Tự động tính'),

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
}
