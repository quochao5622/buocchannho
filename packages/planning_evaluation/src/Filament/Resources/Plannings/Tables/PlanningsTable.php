<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Tables;

use App\Enum\BaseStatusEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use Quochao56\PlanningEvaluation\Models\Planning;

class PlanningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(trans('planning-evaluation::planning.fields.name'))->searchable(),
                TextColumn::make('employee.name')
                    ->label(trans('planning-evaluation::planning.fields.employee'))
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ?: '-'),
                TextColumn::make('student.name')
                    ->label(trans('planning-evaluation::planning.fields.student'))
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ?: '-'),
                TextColumn::make('start_date')->label(trans('planning-evaluation::planning.fields.start_date'))->date('d/m/Y'),
                TextColumn::make('end_date')->label(trans('planning-evaluation::planning.fields.end_date'))->date('d/m/Y'),
                TextColumn::make('status')->label(trans('planning-evaluation::planning.fields.status'))
                ->badge(),
                TextColumn::make('created_at')->label(trans('planning-evaluation::planning.fields.created_at'))->dateTime(),
                TextColumn::make('updated_at')->label(trans('planning-evaluation::planning.fields.updated_at'))->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('evaluate')
                    ->label('Đánh giá')
                    ->color('warning')
                    ->visible(fn (Planning $record): bool => (($record->status?->value ?? $record->status) === BaseStatusEnum::Published->value))
                    ->action(function (Planning $record) {
                        $evaluation = Evaluation::upsertFromPlanning($record);

                        return redirect(EvaluationResource::getUrl('edit', [
                            'planning' => $record,
                            'record' => $evaluation,
                        ]));
                    }),
                EditAction::make(),
                ViewAction::make()
                    ->modalWidth('90%'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                ])
            ]);
    }
}
