<?php

namespace Quochao56\Acl\Filament\Resources\ActivityResource\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('log_name')
                    ->label(trans('acl::activity.fields.log_name'))
                    ->disabled(),
                TextInput::make('description')
                    ->label(trans('acl::activity.fields.description'))
                    ->disabled(),
                TextInput::make('subject_type')
                    ->label(trans('acl::activity.fields.subject_type'))
                    ->disabled(),
                TextInput::make('subject_id')
                    ->label(trans('acl::activity.fields.subject_id'))
                    ->disabled(),
                TextInput::make('causer_type')
                    ->label(trans('acl::activity.fields.causer_type'))
                    ->disabled(),
                TextInput::make('causer_id')
                    ->label(trans('acl::activity.fields.causer_id'))
                    ->disabled(),
                KeyValue::make('properties')
                    ->label(trans('acl::activity.fields.properties'))
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }
}
