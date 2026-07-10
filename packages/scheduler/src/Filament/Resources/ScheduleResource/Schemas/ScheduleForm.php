<?php

namespace Quochao56\Scheduler\Filament\Resources\ScheduleResource\Schemas;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
            Select::make('type')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.type'))
                ->options([
                    'individual' => trans('packages.scheduler::scheduler.schedules.type.individual'),
                    'group' => trans('packages.scheduler::scheduler.schedules.type.group'),
                ])
                ->required()
                ->default('individual')
                ->live()
                ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                    if ($state !== 'individual') {
                        return;
                    }

                    $studentId = $get('student_id');
                    if (! $studentId) {
                        $set('employee_id', null);

                        return;
                    }

                    $teacherId = self::assignedTeacherIdForStudent($studentId);

                    $set('employee_id', $teacherId ?: null);
                }),

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
                ->live()
                ->helperText(function (Get $get): ?string {
                    if ($get('type') !== 'individual') {
                        return null;
                    }

                    $studentId = $get('student_id');
                    if (! $studentId) {
                        return null;
                    }

                    $hasAssignment = self::assignedTeacherIdForStudent($studentId) !== null;

                    return $hasAssignment
                        ? null
                        : trans('packages.scheduler::scheduler.schedules.validation.student_not_assigned_warning');
                })
                ->afterStateUpdated(function ($state, Get $get, Set $set, $livewire): void {
                    if ($get('type') !== 'individual') {
                        return;
                    }

                    if (! $state) {
                        $set('employee_id', null);

                        return;
                    }

                    $assignedTeacherId = self::assignedTeacherIdForStudent($state);

                    $hasAssignment = (bool) $assignedTeacherId;

                    $set('employee_id', $hasAssignment ? $assignedTeacherId : null);

                    if (! $hasAssignment) {
                        Notification::make()
                            ->title(trans('packages.scheduler::scheduler.schedules.validation.student_not_assigned_title'))
                            ->body(trans('packages.scheduler::scheduler.schedules.validation.student_not_assigned_warning'))
                            ->warning()
                            ->send();

                        $livewire?->dispatch('notificationsSent');
                    }
                })
                ->required(fn (Get $get) => $get('type') === 'individual')
                ->visible(fn (Get $get) => $get('type') === 'individual'),

            // Ngày dạy duy nhất – không lặp lại hàng tuần
            DatePicker::make('start_date')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.session_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now())
                ->live()
                ->afterStateUpdated(function ($state, Set $set): void {
                    if ($state) {
                        $carbon = Carbon::parse($state);
                        $set('day_of_week', [$carbon->dayOfWeek === 0 ? 1 : ($carbon->dayOfWeek + 1)]);
                    }
                })
                ->required(),

            Placeholder::make('derived_day_of_week')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.day_of_week'))
                ->content(function (Get $get, ?Model $record): string {
                    $date = $get('start_date') ?: $record?->start_date?->toDateString();

                    if (! $date) {
                        return '-';
                    }

                    $carbon = Carbon::parse($date);
                    $dayOfWeek = $carbon->dayOfWeek === 0 ? 1 : ($carbon->dayOfWeek + 1);

                    return trans("packages.scheduler::scheduler.schedules.day_of_week.{$dayOfWeek}");
                }),

            // Ẩn day_of_week và end_date – tự động tính từ start_date và lưu vào DB
            Hidden::make('day_of_week')
                ->default(function () {
                    $carbon = Carbon::parse(now());

                    return [$carbon->dayOfWeek === 0 ? 1 : ($carbon->dayOfWeek + 1)];
                })
                ->dehydrateStateUsing(function ($state, Get $get) {
                    $date = $get('start_date');
                    if ($date) {
                        $carbon = Carbon::parse($date);

                        return [$carbon->dayOfWeek === 0 ? 1 : ($carbon->dayOfWeek + 1)];
                    }

                    return $state;
                })
                ->dehydrated(true),

            Hidden::make('end_date')
                ->default(null),

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
                    if ($get('type') === 'individual') {
                        $studentId = $get('student_id');

                        if (! $studentId) {
                            return [];
                        }

                        $teacherId = self::assignedTeacherIdForStudent($studentId);

                        if (! $teacherId) {
                            return [];
                        }

                        $teacher = Employee::query()->active()->find($teacherId);

                        return $teacher
                            ? [$teacher->id => $teacher->name]
                            : [];
                    }

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

                    if ($startTime && $endTime && $startDate) {
                        $teachersQuery = $teachersQuery->get()->filter(function ($teacher) use ($startDate, $startTime, $endTime, $record) {
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
                        dayOfWeek: null,
                        startTime: $get('start_time'),
                        endTime: $get('end_time'),
                        startDate: $get('start_date'),
                        endDate: null
                    ),
                    function (string $attribute, $value, $fail) use ($get) {
                        if ($get('type') !== 'individual') {
                            return;
                        }

                        $studentId = $get('student_id');
                        if (! $studentId) {
                            return;
                        }

                        $assignedTeacherId = self::assignedTeacherIdForStudent($studentId);

                        if (! $assignedTeacherId) {
                            return;
                        }

                        if ((int) $value !== (int) $assignedTeacherId) {
                            $fail(trans('packages.scheduler::scheduler.schedules.validation.employee_must_match_assignment'));
                        }
                    },
                ])
                ->helperText(fn (Get $get): ?string => $get('type') === 'individual'
                    ? trans('packages.scheduler::scheduler.schedules.validation.employee_locked_by_assignment')
                    : null)
                ->disabled(fn (Get $get) => $get('type') === 'individual')
                ->dehydrated()
                ->searchable()
                ->required(),

            Select::make('classroom_id')
                ->label(trans('packages.scheduler::scheduler.schedules.fields.classroom_id'))
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

    protected static function assignedTeacherIdForStudent(mixed $studentId): ?int
    {
        if (! $studentId) {
            return null;
        }

        $student = Student::query()
            ->with('currentAssignment:id,student_id,employee_id')
            ->find($studentId);

        return $student?->currentAssignment?->employee_id
            ? (int) $student->currentAssignment->employee_id
            : null;
    }
}
