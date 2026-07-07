<?php

namespace Quochao56\Scheduler\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Quochao56\Scheduler\Filament\Resources\ClassroomResource\Pages\CreateClassroom;
use Quochao56\Scheduler\Filament\Resources\ClassroomResource\Pages\EditClassroom;
use Quochao56\Scheduler\Filament\Resources\ClassroomResource\Pages\ListClassrooms;
use Quochao56\Scheduler\Filament\Resources\ClassroomResource\Schemas\ClassroomForm;
use Quochao56\Scheduler\Filament\Resources\ClassroomResource\Tables\ClassroomTable;
use Quochao56\Scheduler\Models\Classroom;

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;

    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-home-modern';
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.scheduler::scheduler.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return 'Quản lý phòng học';
    }

    public static function getModelLabel(): string
    {
        return 'Phòng học';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Danh sách phòng học';
    }

    public static function form(Schema $schema): Schema
    {
        return ClassroomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassroomTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClassrooms::route('/'),
            'create' => CreateClassroom::route('/create'),
            'edit' => EditClassroom::route('/{record}/edit'),
        ];
    }
}
