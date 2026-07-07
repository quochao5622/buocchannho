<?php

namespace Quochao56\Scheduler\Filament\Resources\AttendanceResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Models\Schedule;
use Quochao56\Student\Models\Student;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('student_id')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.student_id'))
                ->options(Student::active()->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('schedule_id')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.schedule_id'))
                ->options(Schedule::all()->pluck('title', 'id'))
                ->searchable()
                ->nullable(),

            DatePicker::make('attendance_date')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.attendance_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now())
                ->required(),

            Select::make('status')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.status'))
                ->options([
                    'absent_excused' => trans('packages.scheduler::scheduler.attendances.status.absent_excused'),
                    'absent_unexcused' => trans('packages.scheduler::scheduler.attendances.status.absent_unexcused'),
                    'late' => trans('packages.scheduler::scheduler.attendances.status.late'),
                ])
                ->default('absent_excused')
                ->helperText(trans('packages.scheduler::scheduler.attendances.absence_only_help'))
                ->required(),

            DateTimePicker::make('check_in_at')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.check_in_at'))
                ->native(false)
                ->displayFormat('d/m/Y H:i')
                ->nullable(),

            DateTimePicker::make('check_out_at')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.check_out_at'))
                ->native(false)
                ->displayFormat('d/m/Y H:i')
                ->nullable(),

            TextInput::make('total_hours')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.total_hours'))
                ->numeric()
                ->step(0.1)
                ->nullable(),

            Textarea::make('session_note')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.session_note'))
                ->rows(2)
                ->columnSpanFull()
                ->nullable(),

            Textarea::make('absence_reason')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.absence_reason'))
                ->rows(2)
                ->visible(fn (Get $get): bool => in_array($get('status'), ['absent_excused', 'absent_unexcused'], true))
                ->nullable(),

            Select::make('reported_by')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.reported_by'))
                ->options([
                    'parent' => trans('packages.scheduler::scheduler.attendances.reported_by_options.parent'),
                    'teacher' => trans('packages.scheduler::scheduler.attendances.reported_by_options.teacher'),
                    'student' => trans('packages.scheduler::scheduler.attendances.reported_by_options.student'),
                    'other' => trans('packages.scheduler::scheduler.attendances.reported_by_options.other'),
                ])
                ->visible(fn (Get $get): bool => in_array($get('status'), ['absent_excused', 'absent_unexcused'], true))
                ->searchable()
                ->nullable(),

            Toggle::make('make_up_scheduled')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.make_up_scheduled'))
                ->default(false)
                ->visible(fn (Get $get): bool => in_array($get('status'), ['absent_excused', 'absent_unexcused'], true)),

            Select::make('verified_by_employee_id')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.verified_by_employee_id'))
                ->options(Employee::active()->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('actual_employee_id')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.actual_employee_id'))
                ->options(Employee::active()->pluck('name', 'id'))
                ->searchable()
                ->nullable(),

            Textarea::make('notes')
                ->label(trans('packages.scheduler::scheduler.attendances.fields.notes'))
                ->rows(3)
                ->columnSpanFull()
                ->nullable(),
        ]);
    }
}
