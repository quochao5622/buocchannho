<?php

namespace Quochao56\Equipment\Filament\Resources;

use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

    protected static ?int $navigationSort = 10;

    public static function getNavigationIcon(): string|\BackedEnum|\Illuminate\Contracts\Support\Htmlable|null
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Học cụ';
    }

    public static function getNavigationLabel(): string
    {
        return 'Danh mục học cụ';
    }

    public static function getModelLabel(): string
    {
        return 'Danh mục';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Danh mục học cụ';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('Mã danh mục')
                ->maxLength(50)
                ->default(fn () => (new EquipmentCategory())->generateCode()),

            TextInput::make('name') 
                ->label('Tên danh mục')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Textarea::make('description')
                ->label('Mô tả')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('equipments_count')
                    ->counts('equipments')
                    ->label('Số học cụ')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('code', 'asc')
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

