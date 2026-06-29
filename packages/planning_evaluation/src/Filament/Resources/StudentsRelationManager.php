<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Quochao56\Student\Filament\Resources\StudentResource;
use Quochao56\Student\Models\Student;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('packages.planning_evaluation::planning.assignment.students_list') ?? 'Học sinh đang phụ trách';
    }

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('avatar')
                    ->label(trans('packages.student::student.fields.avatar'))
                    ->circular(),
                TextColumn::make('student_code')
                    ->label(trans('packages.student::student.fields.student_code'))
                    ->sortable(),
                TextColumn::make('name')
                    ->label(trans('packages.student::student.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nickname')
                    ->label(trans('packages.student::student.fields.nickname'))
                    ->searchable(),
                TextColumn::make('dob')
                    ->label(trans('packages.student::student.fields.dob'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(trans('packages.student::student.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => trans('packages.student::student.status.active'),
                        'inactive' => trans('packages.student::student.status.inactive'),
                        default => $state,
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Action::make('view_student')
                    ->label(trans('packages.planning_evaluation::planning.tracker.view_detail'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Student $record) => StudentResource::getUrl('edit', [
                        'record' => $record->id,
                    ])),
            ])
            ->bulkActions([
                //
            ]);
    }
}
