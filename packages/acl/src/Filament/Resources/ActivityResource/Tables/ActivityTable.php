<?php

namespace Quochao56\Acl\Filament\Resources\ActivityResource\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(trans('acl::activity.fields.id'))
                    ->sortable(),
                TextColumn::make('log_name')
                    ->label(trans('acl::activity.fields.log_name'))
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->label(trans('acl::activity.fields.activity'))
                    ->searchable(),
                TextColumn::make('causer.name')
                    ->label(trans('acl::activity.fields.causer_name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(trans('acl::activity.fields.created_at'))
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
            ]);
    }
}
