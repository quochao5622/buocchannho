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
use Quochao56\Equipment\Filament\Resources\EquipmentResource\Pages\CreateEquipment;
use Quochao56\Equipment\Filament\Resources\EquipmentResource\Pages\EditEquipment;
use Quochao56\Equipment\Filament\Resources\EquipmentResource\Pages\ListEquipments;
use Quochao56\Equipment\Models\Equipment;
use Quochao56\Equipment\Models\EquipmentCategory;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?int $navigationSort = 11;

    public static function getNavigationIcon(): string|\BackedEnum|\Illuminate\Contracts\Support\Htmlable|null
    {
        return 'heroicon-o-archive-box';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Học cụ';
    }

    public static function getNavigationLabel(): string
    {
        return 'Học cụ';
    }

    public static function getModelLabel(): string
    {
        return 'Học cụ';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Học cụ';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('equipment_code')
                ->label('Mã học cụ')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true)
                ->default(fn () => (new Equipment())->generateCode()),

            TextInput::make('name')
                ->label('Tên học cụ')
                ->required()
                ->maxLength(255),

            Select::make('category_id')
                ->label('Danh mục')
                ->required()
                ->options(fn () => EquipmentCategory::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable(),

            FileUpload::make('image')
                ->label('Hình ảnh')
                ->image()
                ->directory('equipments')
                ->imageEditor(),

            TextInput::make('quantity')
                ->label('Số lượng')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->required(),

            Select::make('status')
                ->label('Trạng thái')
                ->options(Equipment::statusOptions())
                ->default('good')
                ->required(),

            TextInput::make('location')
                ->label('Vị trí')
                ->maxLength(255),

            TextInput::make('unit')
                ->label('Đơn vị tính')
                ->required()
                ->maxLength(50)
                ->default('cái'),

            Textarea::make('note')
                ->label('Ghi chú')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Hình')
                    ->square()
                    ->size(48)
                    ->defaultImageUrl(fn (): string => 'https://placehold.co/96x96?text=HC')
                    ->openUrlInNewTab(),
                TextColumn::make('equipment_code')
                    ->label('Mã')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Danh mục')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Số lượng')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Equipment::statusOptions()[$state] ?? ($state ?? '-')),
                TextColumn::make('location')
                    ->label('Vị trí')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('equipment_code', 'asc')
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Danh mục')
                    ->options(fn () => EquipmentCategory::query()->orderBy('name')->pluck('name', 'id')->all()),
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(Equipment::statusOptions()),
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

