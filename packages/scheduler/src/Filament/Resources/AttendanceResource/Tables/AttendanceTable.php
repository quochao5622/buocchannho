<?php

namespace Quochao56\Scheduler\Filament\Resources\AttendanceResource\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Quochao56\Employee\Models\Employee;
use Quochao56\Student\Models\Student;

class AttendanceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.student_id'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('attendance_date')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.attendance_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('schedule.title')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.schedule_id'))
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('status')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'absent_excused' => 'warning',
                        'absent_unexcused' => 'danger',
                        'late' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => trans("packages.scheduler::scheduler.attendances.status.{$state}")),

                TextColumn::make('check_in_at')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.check_in_at'))
                    ->dateTime('H:i')
                    ->placeholder('-'),

                TextColumn::make('check_out_at')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.check_out_at'))
                    ->dateTime('H:i')
                    ->placeholder('-'),

                TextColumn::make('total_hours')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.total_hours'))
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('session_note')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.session_note'))
                    ->limit(50)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('absence_reason')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.absence_reason'))
                    ->limit(50)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reported_by')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.reported_by'))
                    ->formatStateUsing(fn (?string $state): string => $state ? trans("packages.scheduler::scheduler.attendances.reported_by_options.{$state}") : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('make_up_scheduled')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.make_up_scheduled'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Đã hẹn bù' : 'Chưa hẹn')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('actualEmployee.name')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.actual_employee_id'))
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('verifiedBy.name')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.verified_by_employee_id'))
                    ->placeholder('-')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('student_id')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.student_id'))
                    ->options(Student::pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('actual_employee_id')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.actual_employee_id'))
                    ->options(Employee::pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('status')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.status'))
                    ->options([
                        'absent_excused' => trans('packages.scheduler::scheduler.attendances.status.absent_excused'),
                        'absent_unexcused' => trans('packages.scheduler::scheduler.attendances.status.absent_unexcused'),
                        'late' => trans('packages.scheduler::scheduler.attendances.status.late'),
                    ]),

                SelectFilter::make('make_up_scheduled')
                    ->label(trans('packages.scheduler::scheduler.attendances.fields.make_up_scheduled'))
                    ->options([
                        '1' => 'Đã hẹn bù',
                        '0' => 'Chưa hẹn bù',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->whereIn('status', ['absent_excused', 'absent_unexcused', 'late']))
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_absent_excused')
                        ->label('Đánh dấu Vắng có phép')
                        ->icon('heroicon-o-clock')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'absent_excused'])),

                    BulkAction::make('mark_absent_unexcused')
                        ->label('Đánh dấu Vắng không phép')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'absent_unexcused'])),

                    BulkAction::make('mark_late')
                        ->label('Đánh dấu Đi trễ')
                        ->icon('heroicon-o-clock')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'late'])),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
