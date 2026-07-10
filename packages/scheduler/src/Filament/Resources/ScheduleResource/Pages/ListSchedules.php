<?php

namespace Quochao56\Scheduler\Filament\Resources\ScheduleResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;
use Quochao56\Core\Traits\HasNotifications;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource;
use Quochao56\Scheduler\Models\Classroom;
use Quochao56\Scheduler\Models\Schedule;
use Quochao56\Student\Models\Student;

class ListSchedules extends ListRecords
{
    use HasNotifications;

    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('quickCreate')
                ->label(trans('packages.scheduler::scheduler.schedules.quick_create.label'))
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->visible(fn () => auth()->user()->can('schedules.create'))
                ->form([
                    Select::make('type')
                        ->label(trans('packages.scheduler::scheduler.schedules.quick_create.type'))
                        ->options([
                            'individual' => trans('packages.scheduler::scheduler.schedules.type.individual'),
                            'group' => trans('packages.scheduler::scheduler.schedules.type.group'),
                        ])
                        ->default('individual')
                        ->required()
                        ->live(),

                    Select::make('assignment_mode')
                        ->label(trans('packages.scheduler::scheduler.schedules.quick_create.assignment_mode'))
                        ->options([
                            'group' => trans('packages.scheduler::scheduler.schedules.quick_create.assignment_mode_options.group'),
                            'manual' => trans('packages.scheduler::scheduler.schedules.quick_create.assignment_mode_options.manual'),
                        ])
                        ->default('manual')
                        ->helperText(trans('packages.scheduler::scheduler.schedules.quick_create.assignment_mode_help'))
                        ->required(),

                    Select::make('day_of_week')
                        ->label(trans('packages.scheduler::scheduler.schedules.quick_create.day_of_week'))
                        ->options([
                            2 => trans('packages.scheduler::scheduler.schedules.day_of_week.2'),
                            3 => trans('packages.scheduler::scheduler.schedules.day_of_week.3'),
                            4 => trans('packages.scheduler::scheduler.schedules.day_of_week.4'),
                            5 => trans('packages.scheduler::scheduler.schedules.day_of_week.5'),
                            6 => trans('packages.scheduler::scheduler.schedules.day_of_week.6'),
                            7 => trans('packages.scheduler::scheduler.schedules.day_of_week.7'),
                            1 => trans('packages.scheduler::scheduler.schedules.day_of_week.1'),
                        ])
                        ->multiple()
                        ->required(),

                    TimePicker::make('start_time')
                        ->label(trans('packages.scheduler::scheduler.schedules.quick_create.start_time'))
                        ->native(false)
                        ->displayFormat('H:i:s')
                        ->format('H:i:s')
                        ->seconds(true)
                        ->required(),

                    TimePicker::make('end_time')
                        ->label(trans('packages.scheduler::scheduler.schedules.quick_create.end_time'))
                        ->native(false)
                        ->displayFormat('H:i:s')
                        ->format('H:i:s')
                        ->seconds(true)
                        ->required(),

                    DatePicker::make('start_date')
                        ->label(trans('packages.scheduler::scheduler.schedules.quick_create.start_date'))
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now())
                        ->required(),

                    DatePicker::make('end_date')
                        ->label(trans('packages.scheduler::scheduler.schedules.quick_create.end_date'))
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now()->addMonth())
                        ->required(),

                    Select::make('classroom_id')
                        ->label('Phòng học')
                        ->options(Classroom::active()->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    Select::make('employee_ids')
                        ->label(trans('packages.scheduler::scheduler.schedules.quick_create.employee_ids'))
                        ->options(function () {
                            $teachers = Employee::active()->get();
                            $grouped = [];
                            foreach ($teachers as $teacher) {
                                $posLabel = $teacher->position ?: 'Chưa phân chức vụ';
                                $grouped[$posLabel][$teacher->id] = $teacher->name;
                            }

                            return $grouped;
                        })
                        ->multiple()
                        ->maxItems(fn (Get $get) => $get('type') === 'individual' ? 1 : null)
                        ->suffixActions([
                            Action::make('selectAllEmployees')
                                ->label('Chọn tất cả')
                                ->icon('heroicon-m-check-circle')
                                ->visible(fn (Get $get) => $get('type') !== 'individual')
                                ->action(fn (Select $component) => $component->state(Employee::active()->pluck('id')->toArray())),
                            Action::make('selectEmployeesByPosition')
                                ->label('Theo chức vụ')
                                ->icon('heroicon-m-funnel')
                                ->visible(fn (Get $get) => $get('type') !== 'individual')
                                ->form([
                                    Select::make('position')
                                        ->label('Chức vụ')
                                        ->options(Employee::active()->whereNotNull('position')->pluck('position', 'position')->unique())
                                        ->required(),
                                ])
                                ->action(function (Select $component, array $data) {
                                    $ids = Employee::active()->where('position', $data['position'])->pluck('id')->toArray();
                                    $currentState = $component->getState() ?: [];
                                    $component->state(array_values(array_unique(array_merge($currentState, $ids))));
                                }),
                        ])
                        ->searchable()
                        ->nullable(),

                    Select::make('student_ids')
                        ->label(trans('packages.scheduler::scheduler.schedules.quick_create.student_ids'))
                        ->options(Student::active()->pluck('name', 'id'))
                        ->multiple()
                        ->maxItems(fn (Get $get) => $get('type') === 'individual' ? 1 : null)
                        ->suffixActions([
                            Action::make('selectAllStudents')
                                ->label('Chọn tất cả')
                                ->icon('heroicon-m-check-circle')
                                ->visible(fn (Get $get) => $get('type') !== 'individual')
                                ->action(fn (Select $component) => $component->state(Student::active()->pluck('id')->toArray())),
                        ])
                        ->searchable()
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    $type = $data['type'];
                    $assignmentMode = $data['assignment_mode'] ?? 'round_robin';
                    $title = null;
                    $dayOfWeek = $this->normalizeDays($data['day_of_week'] ?? []);
                    $startTime = $data['start_time'];
                    $endTime = $data['end_time'];
                    $startDate = $data['start_date'];
                    $endDate = $data['end_date'];
                    $classroomId = $data['classroom_id'];
                    $employeeIds = $data['employee_ids'] ?: [];
                    $studentIds = $data['student_ids'] ?: [];

                    if (empty($employeeIds)) {
                        $employeeIds = Employee::active()->pluck('id')->toArray();
                    }
                    if (empty($studentIds)) {
                        if ($type === 'group') {
                            $studentIds = [null];
                        } else {
                            $studentIds = Student::active()->pluck('id')->toArray();
                        }
                    }

                    if (empty($dayOfWeek)) {
                        Notification::make()
                            ->danger()
                            ->title(trans('packages.scheduler::scheduler.schedules.quick_create.validation_error_title'))
                            ->body(trans('packages.scheduler::scheduler.schedules.quick_create.day_of_week_required'))
                            ->send();

                        $this->dispatch('notificationsSent');

                        return;
                    }

                    $successCount = 0;
                    $conflicts = [
                        'teacher' => [],
                        'student' => [],
                        'room' => [],
                    ];

                    $employeeNames = Employee::whereIn('id', array_filter((array) $employeeIds))->pluck('name', 'id');
                    $studentNames = Student::whereIn('id', array_filter((array) $studentIds))->pluck('name', 'id');
                    $roomName = $classroomId ? (Classroom::find($classroomId)?->name ?? 'Phòng học') : null;

                    $attemptCreate = function (?int $studentId, int $employeeId, array $days) use (
                        $title,
                        $type,
                        $startTime,
                        $endTime,
                        $startDate,
                        $endDate,
                        $classroomId,
                        $roomName,
                        $employeeNames,
                        $studentNames,
                        &$successCount,
                        &$conflicts
                    ) {
                        if ($classroomId) {
                            $roomConflict = $this->findConflictSchedule(
                                days: $days,
                                startDate: $startDate,
                                endDate: $endDate,
                                startTime: $startTime,
                                endTime: $endTime,
                                classroomId: $classroomId
                            );

                            if ($roomConflict) {
                                $conflictTitle = ! empty($roomConflict->title) ? $roomConflict->title : (trans('packages.scheduler::scheduler.schedules.model_label').' #'.$roomConflict->id);
                                $this->pushConflict(
                                    conflicts: $conflicts,
                                    type: 'room',
                                    key: (string) $classroomId,
                                    message: trans('packages.scheduler::scheduler.schedules.quick_create.room_conflict_detail', [
                                        'name' => $roomName,
                                        'title' => $conflictTitle,
                                    ])
                                );

                                return;
                            }
                        }

                        $teacherConflict = $this->findConflictSchedule(
                            days: $days,
                            startDate: $startDate,
                            endDate: $endDate,
                            startTime: $startTime,
                            endTime: $endTime,
                            employeeId: $employeeId
                        );

                        if ($teacherConflict) {
                            $teacherName = $employeeNames->get($employeeId) ?? 'Giáo viên';
                            $conflictTitle = ! empty($teacherConflict->title) ? $teacherConflict->title : (trans('packages.scheduler::scheduler.schedules.model_label').' #'.$teacherConflict->id);
                            $this->pushConflict(
                                conflicts: $conflicts,
                                type: 'teacher',
                                key: (string) $employeeId,
                                message: trans('packages.scheduler::scheduler.schedules.quick_create.teacher_conflict_body', [
                                    'name' => $teacherName,
                                    'title' => $conflictTitle,
                                ])
                            );

                            return;
                        }

                        if ($studentId !== null) {
                            $studentConflict = $this->findConflictSchedule(
                                days: $days,
                                startDate: $startDate,
                                endDate: $endDate,
                                startTime: $startTime,
                                endTime: $endTime,
                                studentId: $studentId
                            );

                            if ($studentConflict) {
                                $studentName = $studentNames->get($studentId) ?? 'Học sinh';
                                $conflictTitle = ! empty($studentConflict->title) ? $studentConflict->title : (trans('packages.scheduler::scheduler.schedules.model_label').' #'.$studentConflict->id);
                                $this->pushConflict(
                                    conflicts: $conflicts,
                                    type: 'student',
                                    key: (string) $studentId,
                                    message: trans('packages.scheduler::scheduler.schedules.quick_create.student_conflict_body', [
                                        'name' => $studentName,
                                        'title' => $conflictTitle,
                                    ])
                                );

                                return;
                            }
                        }

                        Schedule::create([
                            'student_id' => $studentId,
                            'employee_id' => $employeeId,
                            'classroom_id' => $classroomId,
                            'title' => $title,
                            'type' => $type,
                            'day_of_week' => $days,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'status' => 'active',
                        ]);

                        $successCount++;
                    };

                    if ($assignmentMode === 'group') {
                        foreach ($employeeIds as $employeeId) {
                            foreach ($studentIds as $studentId) {
                                $sid = $studentId !== null ? (int) $studentId : null;
                                $attemptCreate($sid, (int) $employeeId, $dayOfWeek);
                            }
                        }
                    } elseif ($assignmentMode === 'manual') {
                        foreach ($employeeIds as $employeeId) {
                            foreach ($studentIds as $studentId) {
                                $sid = $studentId !== null ? (int) $studentId : null;
                                $attemptCreate($sid, (int) $employeeId, $dayOfWeek);
                            }
                        }
                    } else {
                        if (empty($employeeIds)) {
                            Notification::make()
                                ->danger()
                                ->title(trans('packages.scheduler::scheduler.schedules.quick_create.validation_error_title'))
                                ->body(trans('packages.scheduler::scheduler.schedules.quick_create.employee_required'))
                                ->send();

                            $this->dispatch('notificationsSent');

                            return;
                        }

                        $teacherCount = count($employeeIds);
                        $assignmentIndex = 0;

                        foreach ($dayOfWeek as $day) {
                            foreach ($studentIds as $studentId) {
                                $teacherId = (int) $employeeIds[$assignmentIndex % $teacherCount];
                                $assignmentIndex++;

                                $sid = $studentId !== null ? (int) $studentId : null;
                                $attemptCreate($sid, (int) $teacherId, [(int) $day]);
                            }
                        }
                    }

                    if ($successCount > 0) {
                        Notification::make()
                            ->success()
                            ->title(trans('packages.scheduler::scheduler.schedules.quick_create.success_title'))
                            ->body(trans('packages.scheduler::scheduler.schedules.quick_create.success_body', ['count' => $successCount]))
                            ->send();
                    }

                    if ($this->hasAnyConflicts($conflicts)) {
                        Notification::make()
                            ->warning()
                            ->title(trans('packages.scheduler::scheduler.schedules.quick_create.conflict_summary_title'))
                            ->body($this->buildConflictSummaryBody($conflicts))
                            ->send();
                    }

                    if ($successCount === 0 && ! $this->hasAnyConflicts($conflicts)) {
                        Notification::make()
                            ->warning()
                            ->title(trans('packages.scheduler::scheduler.schedules.quick_create.no_result_title'))
                            ->body(trans('packages.scheduler::scheduler.schedules.quick_create.no_result_body'))
                            ->send();
                    }

                    $this->dispatch('notificationsSent');
                }),
        ];
    }

    protected function normalizeDays(array $days): array
    {
        $normalized = array_map(fn ($day) => (int) $day, $days);
        $normalized = array_filter($normalized, fn ($day) => $day >= 1 && $day <= 7);

        return array_values(array_unique($normalized));
    }

    protected function findConflictSchedule(
        array $days,
        string $startDate,
        ?string $endDate,
        string $startTime,
        string $endTime,
        ?int $studentId = null,
        ?int $employeeId = null,
        ?int $classroomId = null
    ): ?Schedule {
        $query = Schedule::active();

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }

        $query->where(function (Builder $dayQuery) use ($days) {
            $dayQuery->whereNull('day_of_week');
            foreach ($days as $day) {
                $dayQuery->orWhereJsonContains('day_of_week', (int) $day);
            }
        });

        $query->where(function (Builder $dateQuery) use ($startDate, $endDate) {
            $dateQuery->where(function (Builder $q) use ($startDate) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $startDate);
            });

            if ($endDate) {
                $dateQuery->where('start_date', '<=', $endDate);
            }
        });

        $query->where(function (Builder $timeQuery) use ($startTime, $endTime) {
            $timeQuery->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime);
        });

        return $query->first();
    }

    protected function pushConflict(array &$conflicts, string $type, string $key, string $message): void
    {
        if (! isset($conflicts[$type][$key])) {
            $conflicts[$type][$key] = [];
        }

        if (! in_array($message, $conflicts[$type][$key], true)) {
            $conflicts[$type][$key][] = $message;
        }
    }

    protected function hasAnyConflicts(array $conflicts): bool
    {
        return ! empty($conflicts['teacher']) || ! empty($conflicts['student']) || ! empty($conflicts['room']);
    }

    protected function buildConflictSummaryBody(array $conflicts): string
    {
        $teacherCount = count($conflicts['teacher']);
        $studentCount = count($conflicts['student']);
        $roomCount = count($conflicts['room']);

        $details = [];

        foreach (['teacher', 'student', 'room'] as $type) {
            foreach ($conflicts[$type] as $messages) {
                foreach ($messages as $message) {
                    $details[] = $message;
                    if (count($details) >= 8) {
                        break 3;
                    }
                }
            }
        }

        $summary = trans('packages.scheduler::scheduler.schedules.quick_create.conflict_summary_body', [
            'teacher_count' => $teacherCount,
            'student_count' => $studentCount,
            'room_count' => $roomCount,
        ]);

        if (empty($details)) {
            return $summary;
        }

        return $summary.'<br><br>'.implode('<br>', $details);
    }
}
