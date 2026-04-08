<?php

namespace Quochao56\Equipment\Filament\Resources;

use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Quochao56\Equipment\Filament\Actions\ApproveAction;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Pages\CreateEquipmentInventory;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Pages\EditEquipmentInventory;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Pages\ListEquipmentInventories;
use Quochao56\Equipment\Models\Equipment;
use Quochao56\Equipment\Models\EquipmentInventory;
use Quochao56\Equipment\Models\EquipmentInventoryDetail;
use Illuminate\Support\HtmlString;

class EquipmentInventoryResource extends Resource
{
    protected static ?string $model = EquipmentInventory::class;

    protected static ?int $navigationSort = 12;

    public static function getNavigationIcon(): string|\BackedEnum|\Illuminate\Contracts\Support\Htmlable|null
    {
        return 'heroicon-o-clipboard-document-check';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Học cụ';
    }

    public static function getNavigationLabel(): string
    {
        return 'Kiểm kê học cụ';
    }

    public static function getModelLabel(): string
    {
        return 'Phiếu kiểm kê';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Phiếu kiểm kê';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('inventory_code')
                ->label('Mã phiếu')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true)
                ->default(fn () => (new EquipmentInventory())->generateCode()),

            Hidden::make('inspector_id')
                ->default(fn () => auth()->id()),

            DatePicker::make('inventory_date')
                ->label('Ngày kiểm kê')
                ->native(false)
                ->default(now())
                ->required(),

            Select::make('status')
                ->label('Trạng thái')
                ->options(EquipmentInventory::statusOptions())
                ->default('draft')
                ->required(),

            Textarea::make('notes')
                ->label('Ghi chú')
                ->columnSpanFull(),

            Repeater::make('details')
                ->label('Chi tiết kiểm kê')
                ->relationship()
                ->default(function () {
                    return Equipment::query()
                        ->orderBy('name')
                        ->get()
                        ->map(fn (Equipment $equipment) => [
                            'equipment_id' => $equipment->id,
                            'quantity_expected' => (int) $equipment->quantity,
                            'quantity_actual' => (int) $equipment->quantity,
                            'status' => (string) ($equipment->status ?: 'good'),
                            'notes' => null,
                        ])
                        ->all();
                })
                ->schema([
                    Placeholder::make('equipment_image')
                        ->label('Hình')
                        ->content(function (Get $get): HtmlString {
                            static $imageByEquipmentId = [];

                            $equipmentId = $get('equipment_id');
                            if (! $equipmentId) {
                                return new HtmlString('<div class="w-8 h-8 rounded bg-gray-100"></div>');
                            }

                            if (! array_key_exists($equipmentId, $imageByEquipmentId)) {
                                $imageByEquipmentId[$equipmentId] = Equipment::query()
                                    ->whereKey($equipmentId)
                                    ->value('image');
                            }

                            $image = $imageByEquipmentId[$equipmentId];
                            if (! $image) {
                                return new HtmlString('<div class="w-8 h-8 rounded bg-gray-100"></div>');
                            }

                            $url = Storage::url($image);

                            return new HtmlString(
                                '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">' .
                                '<img src="' . e($url) . '" class="w-8 h-8 rounded object-cover ring-1 ring-gray-200" alt="equipment" loading="lazy"  />' .
                                '</a>'
                            );
                        }),
                    Select::make('equipment_id')
                        ->label('Học cụ')
                        ->relationship('equipment', 'name')
                        ->searchable()
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                    TextInput::make('quantity_expected')
                        ->label('SL dự kiến')
                        ->numeric()
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                    TextInput::make('quantity_actual')
                        ->label('SL thực tế')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                    Select::make('status')
                        ->label('Tình trạng')
                        ->options(EquipmentInventoryDetail::statusOptions())
                        ->required(),
                    Textarea::make('notes')
                        ->label('Ghi chú')
                        ->columnSpanFull(),
                ])
                ->columns(5)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inventory_code')
                    ->label('Mã phiếu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('inventory_date')
                    ->label('Ngày')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('inspector.name')
                    ->label('Người kiểm kê')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => EquipmentInventory::statusOptions()[$state] ?? ($state ?? '-')),
                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('inventory_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
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

    public static function getPages(): array
    {
        return [
            'index' => ListEquipmentInventories::route('/'),
            'create' => CreateEquipmentInventory::route('/create'),
            'edit' => EditEquipmentInventory::route('/{record}/edit'),
        ];
    }
}

