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
                ->default(fn () => (new EquipmentInventory())->generateCode()),

            Hidden::make('inspector_id')
                ->default(fn () => Auth::id()),

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

            Hidden::make('details_buffer')
                ->dehydrated(false),

            TextInput::make('detail_equipment_search')
                ->label(trans('packages.equipment::equipment_inventory_detail.fields.detail_equipment_search'))
                ->placeholder(trans('packages.equipment::equipment_inventory_detail.fields.detail_equipment_search_placeholder'))
                ->live(debounce: 300)
                ->dehydrated(false)
                ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                    $buffer = static::normalizeDetails($get('details_buffer'));
                    $currentDetails = static::normalizeDetails($get('details'));

                    if ($buffer === []) {
                        $buffer = $currentDetails;
                    }

                    $keyword = static::normalizeKeyword($state);

                    if ($keyword === '') {
                        $set('details', $buffer);

                        return;
                    }

                    $set('details', static::filterDetailsByKeyword($buffer, $keyword));
                })
                ->helperText(trans('packages.equipment::equipment_inventory_detail.fields.detail_equipment_search_helper'))
                ->columnSpanFull(),

            Repeater::make('details')
                ->label(trans('packages.equipment::equipment_inventory.fields.details'))
                ->relationship(modifyQueryUsing: function ($query) {
                    return $query
                        ->leftJoin('equipments', 'equipment_inventory_details.equipment_id', '=', 'equipments.id')
                        ->select([
                            'equipment_inventory_details.*',
                            'equipments.equipment_code as equipment_code_snapshot',
                            'equipments.name as equipment_name_snapshot',
                            'equipments.location as equipment_location_snapshot',
                        ])
                        ->orderBy('equipments.name');
                })
                ->default(function () {
                    return Equipment::query()
                        ->orderBy('name')
                        ->get(['id', 'equipment_code', 'name', 'location', 'quantity', 'status'])
                        ->map(fn (Equipment $equipment): array => static::makeDetailStateFromEquipment($equipment))
                        ->all();
                })
                ->afterStateHydrated(function (?array $state, Get $get, Set $set): void {
                    $details = static::normalizeDetails($state);
                    $set('details_buffer', $details);

                    $keyword = static::normalizeKeyword($get('detail_equipment_search'));
                    if ($keyword !== '') {
                        $set('details', static::filterDetailsByKeyword($details, $keyword));
                    }
                })
                ->afterStateUpdated(function (?array $state, ?array $old, Get $get, Set $set): void {
                    $current = static::normalizeDetails($state);
                    $oldVisible = static::normalizeDetails($old);
                    $buffer = static::normalizeDetails($get('details_buffer'));

                    if ($buffer === []) {
                        $buffer = $current;
                    }

                    $keyword = static::normalizeKeyword($get('detail_equipment_search'));
                    $deletedEquipmentIds = static::getDeletedEquipmentIds($oldVisible, $current);

                    $buffer = static::mergeDetailsIntoBuffer($buffer, $current, $deletedEquipmentIds);
                    $set('details_buffer', $buffer);

                    if ($keyword !== '') {
                        $set('details', static::filterDetailsByKeyword($buffer, $keyword));
                    }
                })
                ->beforeStateDehydrated(function (Get $get, Set $set): void {
                    $buffer = static::normalizeDetails($get('details_buffer'));

                    if ($buffer !== []) {
                        $set('details', $buffer);
                    }
                }, shouldUpdateValidatedStateAfter: true)
                ->helperText(trans('packages.equipment::equipment_inventory_detail.fields.detail_equipment_search_helper'))
                ->addable(false)
                ->itemLabel(function (array $state, Get $get): HtmlString|string {
                    $label = static::getDetailLabel($state);
                    $keyword = static::normalizeKeyword($get('detail_equipment_search'));

                    return static::highlightDetailLabel($label, $keyword);
                })
                ->collapsible()
                // ->collapsed()
                ->schema([
                    Hidden::make('equipment_code_snapshot')
                        ->dehydrated(false),
                    Hidden::make('equipment_name_snapshot')
                        ->dehydrated(false),
                    Hidden::make('equipment_location_snapshot')
                        ->dehydrated(false),
                    Placeholder::make('equipment_image')
                        ->label(trans('packages.equipment::equipment_inventory_detail.fields.equipment_image'))
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
                        ->label(trans('packages.equipment::equipment_inventory_detail.fields.equipment'))
                        ->relationship(
                            name: 'equipment',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) => $query->orderBy('name'),
                        )
                        ->getOptionLabelFromRecordUsing(fn (Equipment $record): string => trim(implode(' - ', array_filter([$record->equipment_code, $record->name]))))
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                    TextInput::make('quantity_expected')
                        ->label(trans('packages.equipment::equipment_inventory_detail.fields.quantity_expected'))
                        ->numeric()
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                    TextInput::make('quantity_actual')
                        ->label(trans('packages.equipment::equipment_inventory_detail.fields.quantity_actual'))
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                    Select::make('status')
                        ->label(trans('packages.equipment::equipment_inventory_detail.fields.status'))
                        ->options(EquipmentInventoryDetail::statusOptions())
                        ->required(),
                    Textarea::make('notes')
                        ->label(trans('packages.equipment::equipment_inventory_detail.fields.notes'))
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
                    ->label(trans('packages.equipment::equipment_inventory.fields.inventory_code'))
                    ->searchable()
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

    public static function getPages(): array
    {
        return [
            'index' => ListEquipmentInventories::route('/'),
            'create' => CreateEquipmentInventory::route('/create'),
            'edit' => EditEquipmentInventory::route('/{record}/edit'),
        ];
    }

    /**
     * @param  array<string, mixed>  $detail
     */
    protected static function getDetailLabel(array $detail): string
    {
        return trim(implode(' - ', array_filter([
            $detail['equipment_code_snapshot'] ?? null,
            $detail['equipment_name_snapshot'] ?? null,
            $detail['equipment_location_snapshot'] ?? null,
        ]))) ?: 'Học cụ';
    }

    protected static function makeDetailStateFromEquipment(Equipment $equipment): array
    {
        return [
            'equipment_id' => $equipment->id,
            'equipment_code_snapshot' => $equipment->equipment_code,
            'equipment_name_snapshot' => $equipment->name,
            'equipment_location_snapshot' => $equipment->location,
            'quantity_expected' => (int) $equipment->quantity,
            'quantity_actual' => (int) $equipment->quantity,
            'status' => (string) ($equipment->status ?: 'good'),
            'notes' => null,
        ];
    }

    /**
     * @param  mixed  $details
     * @return array<int, array<string, mixed>>
     */
    protected static function normalizeDetails(mixed $details): array
    {
        if (! is_array($details)) {
            return [];
        }

        return collect($details)
            ->filter(fn ($detail): bool => is_array($detail))
            ->map(fn (array $detail): array => $detail)
            ->values()
            ->all();
    }

    protected static function normalizeKeyword(?string $keyword): string
    {
        return Str::lower(trim((string) $keyword));
    }

    /**
     * @param  array<int, array<string, mixed>>  $details
     * @return array<int, array<string, mixed>>
     */
    protected static function filterDetailsByKeyword(array $details, string $keyword): array
    {
        if ($keyword === '') {
            return $details;
        }

        return collect($details)
            ->filter(fn (array $detail): bool => static::detailMatchesKeyword($detail, $keyword))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $detail
     */
    protected static function detailMatchesKeyword(array $detail, string $keyword): bool
    {
        if ($keyword === '') {
            return true;
        }

        $haystack = Str::lower(implode(' ', array_filter([
            (string) ($detail['equipment_code_snapshot'] ?? ''),
            (string) ($detail['equipment_name_snapshot'] ?? ''),
            (string) ($detail['equipment_location_snapshot'] ?? ''),
        ])));

        return Str::contains($haystack, $keyword);
    }

    /**
     * @param  array<int, array<string, mixed>>  $buffer
     * @param  array<int, array<string, mixed>>  $current
     * @param  array<int, int>  $deletedEquipmentIds
     * @return array<int, array<string, mixed>>
     */
    protected static function mergeDetailsIntoBuffer(array $buffer, array $current, array $deletedEquipmentIds): array
    {
        $byEquipmentId = collect($buffer)
            ->filter(fn (array $detail): bool => filled($detail['equipment_id'] ?? null))
            ->mapWithKeys(fn (array $detail): array => [(int) $detail['equipment_id'] => $detail]);

        foreach ($deletedEquipmentIds as $equipmentId) {
            $byEquipmentId->forget($equipmentId);
        }

        foreach ($current as $detail) {
            $equipmentId = (int) ($detail['equipment_id'] ?? 0);
            if ($equipmentId === 0) {
                continue;
            }

            $byEquipmentId->put($equipmentId, $detail);
        }

        return $byEquipmentId->values()->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $old
     * @param  array<int, array<string, mixed>>  $current
     * @return array<int, int>
     */
    protected static function getDeletedEquipmentIds(array $old, array $current): array
    {
        $oldIds = collect($old)
            ->pluck('equipment_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->values();

        $currentIds = collect($current)
            ->pluck('equipment_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->values();

        return $oldIds
            ->diff($currentIds)
            ->values()
            ->all();
    }

    protected static function highlightDetailLabel(string $label, string $keyword): HtmlString|string
    {
        if ($keyword === '' || $label === '') {
            return $label;
        }

        $matchPosition = mb_stripos($label, $keyword);
        if ($matchPosition === false) {
            return $label;
        }

        $matchLength = mb_strlen($keyword);
        $prefix = mb_substr($label, 0, $matchPosition);
        $match = mb_substr($label, $matchPosition, $matchLength);
        $suffix = mb_substr($label, $matchPosition + $matchLength);

        return new HtmlString(
            e($prefix) . '<mark class="rounded bg-warning-200/40 px-1 text-warning-950 dark:bg-warning-400/40 dark:text-warning-100">' . e($match) . '</mark>' . e($suffix)
        );
    }
}

