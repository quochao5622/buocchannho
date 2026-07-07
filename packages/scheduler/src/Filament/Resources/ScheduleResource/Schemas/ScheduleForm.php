<?php

namespace Quochao56\Scheduler\Filament\Resources\ScheduleResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Models\Classroom;
use Quochao56\Scheduler\Rules\NoConflictScheduleRule;
use Quochao56\Student\Models\Student;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('student_id')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.student_id'))
                ->options(function (?Model $record) {
                    $currentStudentId = $record?->student_id;

                    return Student::query()
                        ->where(function ($q) use ($currentStudentId) {
                            $q->active();
                            if ($currentStudentId) {
                                $q->orWhere('id', $currentStudentId);
                            }
                        })
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->required(),

            TextInput::make('title')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.title'))
                ->required()
                ->maxLength(255),

            Select::make('type')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.type'))
                ->options([
                    'individual' => trans('packages.scheduler::scheduler.schedules.type.individual'),
                    'group' => trans('packages.scheduler::scheduler.schedules.type.group'),
                ])
                ->required()
                ->default('individual'),

            Select::make('day_of_week')
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
                ->multiple()
                ->live()
                ->nullable(),

            DatePicker::make('start_date')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.start_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now())
                ->live()
                ->required(),

            DatePicker::make('end_date')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.end_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->live()
                ->nullable(),

            TimePicker::make('start_time')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.start_time'))
                ->native(false)
                ->displayFormat('H:i:s')
                ->format('H:i:s')
                ->seconds(true)
                ->live()
                ->required(),

            TimePicker::make('end_time')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.end_time'))
                ->native(false)
                ->displayFormat('H:i:s')
                ->format('H:i:s')
                ->seconds(true)
                ->live()
                ->required(),

            Select::make('employee_id')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.employee_id'))
                ->options(function (Get $get, ?Model $record) {
                    $dayOfWeek = $get('day_of_week');
                    $startTime = $get('start_time');
                    $endTime = $get('end_time');
                    $startDate = $get('start_date');

                    $teachersQuery = Employee::query();
                    $currentEmployeeId = $record?->employee_id;
                    $teachersQuery->where(function ($q) use ($currentEmployeeId) {
                        $q->active();
                        if ($currentEmployeeId) {
                            $q->orWhere('id', $currentEmployeeId);
                        }
                    });

                    if ($dayOfWeek && $startTime && $endTime && $startDate) {
                        $teachersQuery = $teachersQuery->get()->filter(function ($teacher) use ($dayOfWeek, $startDate, $startTime, $endTime, $record) {
                            if (is_array($dayOfWeek)) {
                                foreach ($dayOfWeek as $day) {
                                    if (! NoConflictScheduleRule::isTeacherAvailableOnDate($teacher->id, $startDate, $startTime, $endTime, $record?->id)) {
                                        return false;
                                    }
                                }

                                return true;
                            }

                            return NoConflictScheduleRule::isTeacherAvailableOnDate($teacher->id, $startDate, $startTime, $endTime, $record?->id);
                        });
                    } else {
                        $teachersQuery = $teachersQuery->get();
                    }

                    $grouped = [];
                    foreach ($teachersQuery as $teacher) {
                        $posLabel = $teacher->position ?: 'Chưa phân chức vụ';
                        $grouped[$posLabel][$teacher->id] = $teacher->name;
                    }

                    return $grouped;
                })
                ->rules(fn (Get $get, ?Model $record) => [
                    new NoConflictScheduleRule(
                        ignoreId: $record?->id,
                        studentId: $get('student_id'),
                        employeeId: $get('employee_id'),
                        classroomId: $get('classroom_id'),
                        dayOfWeek: $get('day_of_week'),
                        startTime: $get('start_time'),
                        endTime: $get('end_time'),
                        startDate: $get('start_date'),
                        endDate: $get('end_date')
                    ),
                ])
                ->searchable()
                ->required(),

            Select::make('classroom_id')
                ->label('Phòng học')
                ->options(function (?Model $record) {
                    $currentClassroomId = $record?->classroom_id;

                    return Classroom::query()
                        ->where(function ($q) use ($currentClassroomId) {
                            $q->active();
                            if ($currentClassroomId) {
                                $q->orWhere('id', $currentClassroomId);
                            }
                        })
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->nullable(),

            Select::make('status')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.status'))
                ->options([
                    'active' => trans('packages.scheduler::scheduler.schedules.status.active'),
                    'inactive' => trans('packages.scheduler::scheduler.schedules.status.inactive'),
                ])
                ->default('active')
                ->required(),

            Textarea::make('notes')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.notes'))
                ->rows(3)
                ->columnSpanFull()
                ->nullable(),
        ]);
    }
}
