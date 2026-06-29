<?php

namespace Quochao56\Acl\Filament\Resources\RoleResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('acl::rbac.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissions_count')
                    ->label(trans('acl::rbac.fields.permissions_count'))
                    ->counts('permissions'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
