<?php

namespace Quochao56\Scheduler\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Models\Classroom;
use Quochao56\Student\Models\Student;

class CalendarOverview extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    public static function getNavigationLabel(): string
    {
        return trans('packages.scheduler::scheduler.calendar_overview.navigation_label');
    }

    public function getTitle(): string|Htmlable
    {
        return trans('packages.scheduler::scheduler.calendar_overview.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.scheduler::scheduler.navigation_group');
    }

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && $user->hasPermissionTo('calendar_overviews.index');
    }

    protected string $view = 'scheduler::calendar-overview';

    public ?int $employeeId = null;

    public ?int $studentId = null;

    public ?int $classroomId = null;

    public ?string $guardianKeyword = null;

    public function mount(): void
    {
        $user = Auth::user();
        $isManager = $user && $user->can('employees.index');

        if (! $isManager && $user?->employee) {
            $this->employeeId = $user->employee->id;
        }

        $this->form->fill([
            'employeeId' => $this->employeeId,
            'studentId' => $this->studentId,
            'classroomId' => $this->classroomId,
            'guardianKeyword' => $this->guardianKeyword,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $user = Auth::user();
        $isManager = $user && $user->can('employees.index');

        $components = [];

        if ($isManager) {
            $components[] = Select::make('employeeId')
                ->label(trans('packages.scheduler::scheduler.calendar_overview.filters.teacher'))
                ->placeholder(trans('packages.scheduler::scheduler.calendar_overview.filters.all_teachers'))
                ->options(function () {
                    $teachers = Employee::active()->get();
                    $grouped = [];
                    foreach ($teachers as $teacher) {
                        $posLabel = $teacher->position ?: 'Chưa phân chức vụ';
                        $grouped[$posLabel][$teacher->id] = $teacher->name;
                    }

                    return $grouped;
                })
                ->searchable()
                ->live()
                ->afterStateUpdated(fn ($state) => $this->employeeId = $state);
        }

        $components[] = Select::make('studentId')
            ->label(trans('packages.scheduler::scheduler.calendar_overview.filters.student'))
            ->placeholder(trans('packages.scheduler::scheduler.calendar_overview.filters.all_students'))
            ->options(function () use ($isManager, $user) {
                $query = Student::active();
                if (! $isManager && $user?->employee) {
                    $query->whereHas('currentAssignment', function ($q) use ($user) {
                        $q->where('employee_id', $user->employee->id);
                    });
                }

                return $query->pluck('name', 'id');
            })
            ->searchable()
            ->live()
            ->afterStateUpdated(fn ($state) => $this->studentId = $state);

        $components[] = Select::make('classroomId')
            ->label(trans('packages.scheduler::scheduler.calendar_overview.filters.classroom'))
            ->placeholder(trans('packages.scheduler::scheduler.calendar_overview.filters.all_classrooms'))
            ->options(Classroom::active()->pluck('name', 'id'))
            ->searchable()
            ->live()
            ->afterStateUpdated(fn ($state) => $this->classroomId = $state);

        $components[] = TextInput::make('guardianKeyword')
            ->label(trans('packages.scheduler::scheduler.calendar_overview.filters.guardian_keyword'))
            ->placeholder(trans('packages.scheduler::scheduler.calendar_overview.filters.guardian_keyword_placeholder'))
            ->live(debounce: 600)
            ->afterStateUpdated(fn ($state) => $this->guardianKeyword = $state);

        return $schema->components($components)->columns([
            'sm' => 1,
            'md' => 4,
        ]);
    }
}
