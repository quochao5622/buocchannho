<?php

namespace Quochao56\Scheduler\Filament\Resources\ScheduleResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Models\Classroom;
use Quochao56\Student\Models\Student;

class ScheduleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(trans('packages.scheduler::scheduler.schedules.fields.title'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.name')
                    ->label(trans('packages.scheduler::scheduler.schedules.fields.student_id'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.name')
                    ->label(trans('packages.scheduler::scheduler.schedules.fields.employee_id'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('classroom.name')
                    ->label('Phòng học')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('type')
                    ->label(trans('packages.scheduler::scheduler.schedules.fields.type'))
                    ->formatStateUsing(fn (string $state): string => trans("packages.scheduler::scheduler.schedules.type.{$state}")),

                TextColumn::make('day_of_week')
                    ->label(trans('packages.scheduler::scheduler.schedules.fields.day_of_week'))
                    ->wrap()
                    ->formatStateUsing(function ($state): string {
                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $state = $decoded;
                            }
                        }

                        $days = match (true) {
                            is_array($state) => $state,
                            is_int($state), is_string($state) => [(int) $state],
                            default => [],
                        };

                        $days = array_values(array_filter($days, fn ($day) => (int) $day >= 1 && (int) $day <= 7));

                        if (empty($days)) {
                            return '-';
                        }

                        return collect($days)
                            ->map(fn ($day) => trans("packages.scheduler::scheduler.schedules.day_of_week.{$day}"))
                            ->implode(', ');
                    }),

                TextColumn::make('start_time')
                    ->label(trans('packages.scheduler::scheduler.schedules.fields.start_time'))
                    ->time('H:i:s')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label(trans('packages.scheduler::scheduler.schedules.fields.end_time'))
                    ->time('H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label(trans('packages.scheduler::scheduler.schedules.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => trans("packages.scheduler::scheduler.schedules.status.{$state}"))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('student_id')
                    ->label(trans('packages.scheduler::scheduler.schedules.fields.student_id'))
                    ->options(Student::pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('employee_id')
                    ->label(trans('packages.scheduler::scheduler.schedules.fields.employee_id'))
                    ->options(Employee::pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('status')
                    ->label(trans('packages.scheduler::scheduler.schedules.fields.status'))
                    ->options([
                        'active' => trans('packages.scheduler::scheduler.schedules.status.active'),
                        'inactive' => trans('packages.scheduler::scheduler.schedules.status.inactive'),
                    ]),

                SelectFilter::make('classroom_id')
                    ->label('Phòng học')
                    ->options(Classroom::pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('day_of_week')
                    ->label(trans('packages.scheduler::scheduler.schedules.fields.day_of_week'))
                    ->options([
                        1 => trans('packages.scheduler::scheduler.schedules.day_of_week.1'),
                        2 => trans('packages.scheduler::scheduler.schedules.day_of_week.2'),
                        3 => trans('packages.scheduler::scheduler.schedules.day_of_week.3'),
                        4 => trans('packages.scheduler::scheduler.schedules.day_of_week.4'),
                        5 => trans('packages.scheduler::scheduler.schedules.day_of_week.5'),
                        6 => trans('packages.scheduler::scheduler.schedules.day_of_week.6'),
                        7 => trans('packages.scheduler::scheduler.schedules.day_of_week.7'),
                    ])
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereJsonContains('day_of_week', (int) $data['value']);
                        }
                    }),

                Filter::make('date_range')
                    ->label('Khoảng ngày hiệu lực')
                    ->form([
                        DatePicker::make('from')
                            ->label('Từ ngày')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('to')
                            ->label('Đến ngày')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['from']) && empty($data['to'])) {
                            return $query;
                        }

                        $from = $data['from'] ?? '1900-01-01';
                        $to = $data['to'] ?? '9999-12-31';

                        return $query
                            ->whereDate('start_date', '<=', $to)
                            ->where(function ($dateQuery) use ($from) {
                                $dateQuery->whereNull('end_date')
                                    ->orWhereDate('end_date', '>=', $from);
                            });
                    }),
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
