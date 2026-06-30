<?php

namespace Quochao56\Employee\Filament\Resources\EmployeeResource\Tables;

use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Quochao56\Core\Enum\BaseStatusEnum;

class EmployeeTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label(trans('packages.employee::employee.fields.avatar'))
                    ->circular(),
                TextColumn::make('employee_code')
                    ->label(trans('packages.employee::employee.fields.employee_code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label(trans('packages.employee::employee.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(trans('packages.employee::employee.fields.email'))
                    ->searchable(),

                TextColumn::make('gender')
                    ->label(trans('packages.employee::employee.fields.gender'))
                    ->formatStateUsing(fn (?string $state): string => trans('packages.core::core.gender.'.$state ?? 'male')),

                TextColumn::make('phone')
                    ->label(trans('packages.employee::employee.fields.phone')),

                TextColumn::make('position')
                    ->label(trans('packages.employee::employee.fields.position'))
                    ->searchable(),

                TextColumn::make('employment_type')
                    ->label(trans('packages.employee::employee.fields.employment_type'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'full-time' => 'success',
                        'part-time' => 'warning',
                        'intern' => 'info',
                        'contract' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'full-time' => trans('packages.employee::employee.employment_type.full_time'),
                        'part-time' => trans('packages.employee::employee.employment_type.part_time'),
                        'intern' => trans('packages.employee::employee.employment_type.intern'),
                        'contract' => trans('packages.employee::employee.employment_type.contract'),
                        default => $state ?? '-',
                    }),

                TextColumn::make('students_count')
                    ->counts('students')
                    ->label(trans('packages.employee::employee.fields.students_count'))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('hired_at')
                    ->label(trans('packages.employee::employee.fields.hired_at'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(trans('packages.employee::employee.fields.status'))
                    ->badge()
                    ->color(fn ($state): string => $state instanceof BaseStatusEnum ? $state->getColor() : ($state === 'active' ? 'success' : 'danger')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('packages.employee::employee.fields.status'))
                    ->options([
                        BaseStatusEnum::Active->value => BaseStatusEnum::Active->getLabel(),
                        BaseStatusEnum::Inactive->value => BaseStatusEnum::Inactive->getLabel(),
                    ]),

                SelectFilter::make('employment_type')
                    ->label(trans('packages.employee::employee.fields.employment_type'))
                    ->options([
                        'full-time' => trans('packages.employee::employee.employment_type.full_time'),
                        'part-time' => trans('packages.employee::employee.employment_type.part_time'),
                        'intern' => trans('packages.employee::employee.employment_type.intern'),
                        'contract' => trans('packages.employee::employee.employment_type.contract'),
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                ActionsBulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
