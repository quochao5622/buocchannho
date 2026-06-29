<?php

namespace Quochao56\PlanningEvaluation\Filament\Pages;

use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Quochao56\Employee\Models\Employee;
use Quochao56\Student\Models\Student;

class StudentProgressReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    public static function getNavigationLabel(): string
    {
        return trans('packages.planning_evaluation::planning.progress.nav_label');
    }

    public function getTitle(): string|Htmlable
    {
        return trans('packages.planning_evaluation::planning.progress.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.planning_evaluation::planning.navigation_group');
    }

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermissionTo('plannings.progress'));
    }

    protected string $view = 'planning-evaluation::student-progress-report';

    public ?int $studentId = null;

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo = now()->endOfYear()->format('Y-m-d');

        $this->form->fill([
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('studentId')
                    ->label(trans('packages.planning_evaluation::planning.progress.select_student'))
                    ->placeholder(trans('packages.planning_evaluation::planning.progress.placeholder'))
                    ->options(function () {
                        $query = Student::query()->where('status', 'active');

                        if (auth()->check() && !auth()->user()->isSuperAdmin()) {
                            $canManageAll = auth()->user()->hasPermissionTo('employees.index')
                                || auth()->user()->hasPermissionTo('employees.edit');

                            if (!$canManageAll) {
                                $employee = Employee::where('email', auth()->user()->email)->first();
                                if ($employee) {
                                    $query->whereHas('currentAssignment', function ($q) use ($employee) {
                                        $q->where('employee_id', $employee->id);
                                    });
                                } else {
                                    $query->whereRaw('1=0');
                                }
                            }
                        }

                        return $query->orderBy('name')->pluck('name', 'id');
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->studentId = $state),

                Grid::make(2)->schema([
                    DatePicker::make('dateFrom')
                        ->label('Từ ngày')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now()->startOfYear())
                        ->live()
                        ->afterStateUpdated(fn ($state) => $this->dateFrom = $state),

                    DatePicker::make('dateTo')
                        ->label('Đến ngày')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now()->endOfYear())
                        ->live()
                        ->afterStateUpdated(fn ($state) => $this->dateTo = $state),
                ]),
            ]);
    }
}
