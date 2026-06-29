<?php

namespace Quochao56\Acl\Filament\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Quochao56\Acl\Filament\Resources\ActivityResource\Pages\ListActivities;
use Quochao56\Acl\Filament\Resources\ActivityResource\Pages\ViewActivity;
use Quochao56\Acl\Filament\Resources\ActivityResource\Schemas\ActivityForm;
use Quochao56\Acl\Filament\Resources\ActivityResource\Tables\ActivityTable;
use Spatie\Activitylog\Models\Activity;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getNavigationGroup(): ?string
    {
        return trans('acl::activity.resource.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return trans('acl::activity.resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('acl::activity.resource.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return ActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'view' => ViewActivity::route('/{record}'),
        ];
    }
}
