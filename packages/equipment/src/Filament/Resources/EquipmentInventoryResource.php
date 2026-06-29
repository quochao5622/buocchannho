<?php

namespace Quochao56\Equipment\Filament\Resources;

use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Quochao56\Equipment\Filament\Actions\ApproveAction;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Pages\CreateEquipmentInventory;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Pages\EditEquipmentInventory;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Pages\ListEquipmentInventories;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\RelationManagers\DetailsRelationManager;
use Quochao56\Equipment\Models\Equipment;
use Quochao56\Equipment\Models\EquipmentInventory;
use Quochao56\Equipment\Models\EquipmentInventoryDetail;
use Illuminate\Support\HtmlString;
use Quochao56\Core\Support\VietnameseSearch;

class EquipmentInventoryResource extends Resource
{
    protected static ?string $model = EquipmentInventory::class;

    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string|\BackedEnum|\Illuminate\Contracts\Support\Htmlable|null
    {
        return 'heroicon-o-clipboard-document-check';
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.equipment::equipment.common.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.equipment::equipment_inventory.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.equipment::equipment_inventory.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.equipment::equipment_inventory.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('inventory_code')
                ->label(trans('packages.equipment::equipment_inventory.fields.inventory_code'))
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true)
                ->default(fn() => (new EquipmentInventory())->generateCode()),

            Hidden::make('inspector_id')
                ->default(fn() => Auth::id()),

            DatePicker::make('inventory_date')
                ->label(trans('packages.equipment::equipment_inventory.fields.inventory_date'))
                ->native(false)
                ->default(now())
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

    public static function table(Table $table): Table
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
                    ->formatStateUsing(fn(?string $state): string => EquipmentInventory::statusOptions()[$state] ?? ($state ?? '-')),
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

    public static function getRelations(): array
    {
        return [
            DetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEquipmentInventories::route('/'),
            'create' => CreateEquipmentInventory::route('/create'),
            'edit' => EditEquipmentInventory::route('/{record}/edit'),
        ];
    }
}
