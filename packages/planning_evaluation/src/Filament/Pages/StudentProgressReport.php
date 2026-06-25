<?php

namespace Quochao56\PlanningEvaluation\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Quochao56\Student\Models\Student;

class StudentProgressReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    public static function getNavigationLabel(): string
    {
        return trans('packages.planning_evaluation::planning.progress.nav_label');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return trans('packages.planning_evaluation::planning.progress.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.planning_evaluation::planning.navigation_group');
    }

    protected static ?int $navigationSort = 5;

    protected string $view = 'planning-evaluation::student-progress-report';

    public ?int $studentId = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('studentId')
                    ->label(trans('packages.planning_evaluation::planning.progress.select_student'))
                    ->placeholder(trans('packages.planning_evaluation::planning.progress.placeholder'))
                    ->options(Student::query()->where('status', 'active')->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->studentId = $state),
            ]);
    }
}
