<?php

namespace Quochao56\Scheduler\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Models\Classroom;
use Quochao56\Scheduler\Models\Schedule;
use Quochao56\Scheduler\Models\ScheduleException;
use Quochao56\Student\Models\Student;

class DailyOperationPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 4;

    protected string $view = 'scheduler::daily-operation';

    public ?string $selectedDate = null;

    public ?int $employeeId = null;

    public ?int $studentId = null;

    public ?int $classroomId = null;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && $user->hasPermissionTo('daily_operations.index');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.scheduler::scheduler.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.scheduler::scheduler.daily_operations.navigation_label');
    }

    public function getTitle(): string|Htmlable
    {
        return trans('packages.scheduler::scheduler.daily_operations.title');
    }

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();

        $user = Auth::user();
        $isManager = $user && $user->can('employees.index');

        if (! $isManager && $user?->employee) {
            $this->employeeId = $user->employee->id;
        }

        $this->form->fill([
            'selectedDate' => $this->selectedDate,
            'employeeId' => $this->employeeId,
            'studentId' => $this->studentId,
            'classroomId' => $this->classroomId,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $user = Auth::user();
        $isManager = $user && $user->can('employees.index');

        $components = [
            DatePicker::make('selectedDate')
                ->label(trans('packages.scheduler::scheduler.daily_operations.selected_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->required()
                ->live()
                ->afterStateUpdated(fn ($state) => $this->selectedDate = $state),
        ];

        if ($isManager) {
            $components[] = Select::make('employeeId')
                ->label(trans('packages.scheduler::scheduler.daily_operations.teacher'))
                ->placeholder(trans('packages.scheduler::scheduler.daily_operations.all_teachers'))
                ->options(Employee::active()->pluck('name', 'id'))
                ->searchable()
                ->live()
                ->afterStateUpdated(fn ($state) => $this->employeeId = $state);
        }

        $components[] = Select::make('studentId')
            ->label(trans('packages.scheduler::scheduler.daily_operations.student'))
            ->placeholder(trans('packages.scheduler::scheduler.daily_operations.all_students'))
            ->options(function () use ($isManager, $user) {
                $query = Student::active();
                if (! $isManager && $user?->employee) {
                    $employeeId = (int) $user->employee->id;

                    $query->where(function ($studentQuery) use ($employeeId) {
                        $studentQuery->whereHas('currentAssignment', function ($q) use ($employeeId) {
                            $q->where('employee_id', $employeeId);
                        })->orWhereIn('id', function ($scheduleQuery) use ($employeeId) {
                            $scheduleQuery->select('student_id')
                                ->from('schedules')
                                ->whereExists(function ($subQuery) use ($employeeId) {
                                    $subQuery->selectRaw('1')
                                        ->from('schedule_exceptions')
                                        ->whereColumn('schedule_exceptions.schedule_id', 'schedules.id')
                                        ->where('schedule_exceptions.action', 'substitute')
                                        ->where('schedule_exceptions.new_employee_id', $employeeId);
                                });
                        });
                    });
                }

                return $query->pluck('name', 'id');
            })
            ->searchable()
            ->live()
            ->afterStateUpdated(fn ($state) => $this->studentId = $state);

        $components[] = Select::make('classroomId')
            ->label(trans('packages.scheduler::scheduler.daily_operations.classroom'))
            ->placeholder(trans('packages.scheduler::scheduler.daily_operations.all_classrooms'))
            ->options(Classroom::active()->pluck('name', 'id'))
            ->searchable()
            ->live()
            ->afterStateUpdated(fn ($state) => $this->classroomId = $state);

        return $schema->components($components)->columns([
            'sm' => 1,
            'md' => 4,
        ]);
    }

    public function getViewData(): array
    {
        $rows = $this->buildDailyRows();

        $summary = [
            'total' => count($rows),
            'canceled' => collect($rows)->where('schedule_status', 'canceled')->count(),
            'rescheduled' => collect($rows)->where('schedule_status', 'rescheduled')->count(),
        ];

        return [
            'rows' => $rows,
            'summary' => $summary,
        ];
    }

    protected function buildDailyRows(): array
    {
        $user = Auth::user();
        $isManager = $user && ($user->isSuperAdmin() || $user->hasRole('Quản lý'));

        if (! $isManager && $user?->employee) {
            $this->employeeId = $user->employee->id;
        }

        $date = Carbon::parse($this->selectedDate ?: now()->toDateString());
        $dateStr = $date->toDateString();
        $dayOfWeek = $date->dayOfWeek === 0 ? 1 : ($date->dayOfWeek + 1);

        $schedulesQuery = Schedule::query()
            ->active()
            ->with(['student', 'employee', 'classroom'])
            ->whereDate('start_date', '<=', $dateStr)
            ->where(function ($query) use ($dateStr) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $dateStr);
            });

        if ($this->employeeId) {
            $employeeId = (int) $this->employeeId;

            $schedulesQuery->where(function ($query) use ($employeeId, $dateStr) {
                $query->where('employee_id', $employeeId)
                    ->orWhereHas('exceptions', function ($sub) use ($employeeId, $dateStr) {
                        $sub->where('action', 'substitute')
                            ->where('new_employee_id', $employeeId)
                            ->whereDate('exception_date', $dateStr);
                    });
            });
        }
        if ($this->studentId) {
            $schedulesQuery->where('student_id', $this->studentId);
        }
        if ($this->classroomId) {
            $schedulesQuery->where('classroom_id', $this->classroomId);
        }

        $schedules = $schedulesQuery->get();

        $exceptions = ScheduleException::query()
            ->with(['newEmployee', 'newClassroom'])
            ->where(function ($query) use ($dateStr) {
                $query->whereDate('exception_date', $dateStr)
                    ->orWhereDate('new_exception_date', $dateStr);
            })
            ->whereIn('schedule_id', $schedules->pluck('id'))
            ->get()
            ->keyBy('schedule_id');

        $rows = [];

        foreach ($schedules as $schedule) {
            $days = $this->normalizeDayValues($schedule->day_of_week);
            $hasRegularSession = empty($days)
                ? $schedule->start_date?->toDateString() === $dateStr
                : in_array($dayOfWeek, $days, true);

            $exception = $exceptions->get($schedule->id);
            $hasRescheduleSession = $exception
                && $exception->action === 'reschedule'
                && $exception->new_exception_date?->toDateString() === $dateStr;

            if (
                $exception
                && $exception->action === 'reschedule'
                && $exception->exception_date?->toDateString() === $dateStr
                && $exception->new_exception_date
                && $exception->new_exception_date->toDateString() !== $dateStr
            ) {
                continue;
            }

            if (! $hasRegularSession && ! $hasRescheduleSession) {
                continue;
            }

            $teacherName = $schedule->employee?->name ?? '-';
            $roomName = $schedule->classroom?->name ?? '-';
            $startTime = $schedule->start_time;
            $endTime = $schedule->end_time;
            $scheduleStatus = 'normal';

            if ($exception) {
                if ($exception->action === 'cancel') {
                    $scheduleStatus = 'canceled';
                }

                if ($exception->action === 'substitute') {
                    $scheduleStatus = 'substituted';
                    $teacherName = $exception->newEmployee?->name ?? $teacherName;
                }

                if ($exception->action === 'reschedule') {
                    $scheduleStatus = 'rescheduled';
                    $startTime = $exception->new_start_time ?? $startTime;
                    $endTime = $exception->new_end_time ?? $endTime;
                    $roomName = $exception->newClassroom?->name ?? $roomName;
                }
            }

            if ($this->employeeId) {
                $teachingEmployeeId = (int) $schedule->employee_id;
                if ($exception && $exception->action === 'substitute' && $exception->new_employee_id) {
                    $teachingEmployeeId = (int) $exception->new_employee_id;
                }

                if ((int) $this->employeeId !== $teachingEmployeeId) {
                    continue;
                }
            }

            $rowTitle = $schedule->title ?? ($schedule->type === 'group' ? 'Dạy nhóm' : 'Can thiệp cá nhân 1-1');

            $rows[] = [
                'time' => substr((string) $startTime, 0, 5).' - '.substr((string) $endTime, 0, 5),
                'title' => $rowTitle,
                'student' => $schedule->student?->name ?? '-',
                'teacher' => $teacherName,
                'classroom' => $roomName,
                'schedule_status' => $scheduleStatus,
                'notes' => $exception?->reason ?? '-',
            ];
        }

        usort($rows, fn (array $a, array $b) => strcmp($a['time'], $b['time']));

        return $rows;
    }

    protected function normalizeDayValues(mixed $days): array
    {
        if (is_string($days)) {
            $decoded = json_decode($days, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $days = $decoded;
            }
        }

        if (! is_array($days)) {
            return [];
        }

        return collect($days)
            ->map(fn ($day) => (int) $day)
            ->filter(fn ($day) => $day >= 1 && $day <= 7)
            ->values()
            ->all();
    }
}
