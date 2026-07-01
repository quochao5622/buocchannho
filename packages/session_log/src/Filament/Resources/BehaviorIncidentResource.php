<?php

namespace Quochao56\SessionLog\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource\Pages\CreateBehaviorIncident;
use Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource\Pages\EditBehaviorIncident;
use Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource\Pages\ListBehaviorIncidents;
use Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource\Schemas\BehaviorIncidentForm;
use Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource\Tables\BehaviorIncidentTable;
use Quochao56\SessionLog\Models\BehaviorIncident;

class BehaviorIncidentResource extends Resource
{
    protected static ?string $model = BehaviorIncident::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-fire';
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.session_log::behavior_incident.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.session_log::behavior_incident.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.session_log::behavior_incident.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.session_log::behavior_incident.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return BehaviorIncidentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BehaviorIncidentTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBehaviorIncidents::route('/'),
            'create' => CreateBehaviorIncident::route('/create'),
            'edit' => EditBehaviorIncident::route('/{record}/edit'),
        ];
    }
}
