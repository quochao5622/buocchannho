<?php

namespace Quochao56\PlanningEvaluation\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\Student\Models\Student;

class PlanningEvaluationTracker extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    public static function getNavigationLabel(): string
    {
        return trans('packages.planning_evaluation::planning.tracker.nav_label');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return trans('packages.planning_evaluation::planning.tracker.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.planning_evaluation::planning.navigation_group');
    }

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermissionTo('plannings.tracker'));
    }

    protected string $view = 'planning-evaluation::tracker';

    public function table(Table $table): Table
    {
        return $table
            ->query(Student::query()->where('status', 'active'))
            ->columns([
                TextColumn::make('student_code')
                    ->label(trans('packages.planning_evaluation::planning.tracker.student_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(trans('packages.planning_evaluation::planning.tracker.student_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nickname')
                    ->label(trans('packages.planning_evaluation::planning.tracker.nickname'))
                    ->searchable(),
                TextColumn::make('currentTeacher.name')
                    ->label(trans('packages.planning_evaluation::planning.tracker.managing_teacher'))
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label(trans('packages.planning_evaluation::planning.tracker.status'))
                    ->badge()
                    ->state(function (Student $record, $livewire) {
                        $filters = $livewire->tableFilters;
                        $type = $filters['type']['value'] ?? 'planning';
                        $timeRange = $filters['time_range'] ?? [];
                        $fromDate = $timeRange['from_date'] ?? null;
                        $toDate = $timeRange['to_date'] ?? null;

                        if ($type === 'planning') {
                            $exists = Planning::query()
                                ->where('student_id', $record->id)
                                ->where(function ($q) use ($fromDate, $toDate) {
                                    if ($fromDate) {
                                        $q->where('end_date', '>=', $fromDate);
                                    }
                                    if ($toDate) {
                                        $q->where('start_date', '<=', $toDate);
                                    }
                                })
                                ->exists();
                        } else {
                            $exists = Evaluation::query()
                                ->whereHas('planning', function ($q) use ($record, $fromDate, $toDate) {
                                    $q->where('student_id', $record->id)
                                      ->where(function ($sq) use ($fromDate, $toDate) {
                                          if ($fromDate) {
                                              $sq->where('end_date', '>=', $fromDate);
                                          }
                                          if ($toDate) {
                                              $sq->where('start_date', '<=', $toDate);
                                          }
                                      });
                                })->exists();
                        }

                        return $exists ? trans('packages.planning_evaluation::planning.tracker.submitted') : trans('packages.planning_evaluation::planning.tracker.not_submitted');
                    })
                    ->color(fn ($state) => $state === trans('packages.planning_evaluation::planning.tracker.submitted') ? 'success' : 'danger'),
                TextColumn::make('actual_teacher')
                    ->label(trans('packages.planning_evaluation::planning.tracker.actual_teacher'))
                    ->state(function (Student $record, $livewire) {
                        $filters = $livewire->tableFilters;
                        $type = $filters['type']['value'] ?? 'planning';
                        $timeRange = $filters['time_range'] ?? [];
                        $fromDate = $timeRange['from_date'] ?? null;
                        $toDate = $timeRange['to_date'] ?? null;

                        if ($type === 'planning') {
                            $plan = Planning::query()
                                ->where('student_id', $record->id)
                                ->where(function ($q) use ($fromDate, $toDate) {
                                    if ($fromDate) {
                                        $q->where('end_date', '>=', $fromDate);
                                    }
                                    if ($toDate) {
                                        $q->where('start_date', '<=', $toDate);
                                    }
                                })
                                ->first();

                            return $plan?->employee?->name ?? '-';
                        } else {
                            $evaluation = Evaluation::query()
                                ->whereHas('planning', function ($q) use ($record, $fromDate, $toDate) {
                                    $q->where('student_id', $record->id)
                                      ->where(function ($sq) use ($fromDate, $toDate) {
                                          if ($fromDate) {
                                              $sq->where('end_date', '>=', $fromDate);
                                          }
                                          if ($toDate) {
                                              $sq->where('start_date', '<=', $toDate);
                                          }
                                      });
                                })->first();

                            return $evaluation?->planning?->employee?->name ?? '-';
                        }
                    }),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(trans('packages.planning_evaluation::planning.tracker.type'))
                    ->options([
                        'planning' => trans('packages.planning_evaluation::planning.tracker.planning'),
                        'evaluation' => trans('packages.planning_evaluation::planning.tracker.evaluation'),
                    ])
                    ->default('planning')
                    ->selectablePlaceholder(false)
                    ->query(fn ($query) => $query),
                \Filament\Tables\Filters\Filter::make('time_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from_date')
                            ->label(trans('packages.planning_evaluation::planning.clone.start_date'))
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->startOfMonth()),
                        \Filament\Forms\Components\DatePicker::make('to_date')
                            ->label(trans('packages.planning_evaluation::planning.clone.end_date'))
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->endOfMonth()),
                    ])
                    ->query(fn ($query) => $query),
                SelectFilter::make('managing_teacher')
                    ->label(trans('packages.planning_evaluation::planning.tracker.managing_teacher'))
                    ->options(Employee::query()->where('status', \App\Enum\BaseStatusEnum::Active->value ?? \App\Enum\BaseStatusEnum::Active)->pluck('name', 'id'))
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return;
                        }
                        $query->whereHas('currentAssignment', function ($q) use ($data) {
                            $q->where('employee_id', $data['value']);
                        });
                    }),
                TernaryFilter::make('my_students')
                    ->label(trans('packages.planning_evaluation::planning.tracker.my_students'))
                    ->placeholder(trans('packages.planning_evaluation::planning.tracker.all_students'))
                    ->trueLabel(trans('packages.planning_evaluation::planning.tracker.yes'))
                    ->falseLabel(trans('packages.planning_evaluation::planning.tracker.no'))
                    ->queries(
                        true: function ($query) {
                            $employee = Employee::where('email', auth()->user()->email)->first();
                            if ($employee) {
                                $query->whereHas('currentAssignment', function ($q) use ($employee) {
                                    $q->where('employee_id', $employee->id);
                                });
                            } else {
                                $query->whereRaw('1=0');
                            }
                        },
                        false: fn ($query) => $query,
                    ),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Action::make('view_record')
                    ->label(trans('packages.planning_evaluation::planning.tracker.view_detail'))
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->url(function (Student $record, $livewire) {
                        $filters = $livewire->tableFilters;
                        $type = $filters['type']['value'] ?? 'planning';
                        $timeRange = $filters['time_range'] ?? [];
                        $fromDate = $timeRange['from_date'] ?? null;
                        $toDate = $timeRange['to_date'] ?? null;

                        if ($type === 'planning') {
                            $plan = Planning::query()
                                ->where('student_id', $record->id)
                                ->where(function ($q) use ($fromDate, $toDate) {
                                    if ($fromDate) {
                                        $q->where('end_date', '>=', $fromDate);
                                    }
                                    if ($toDate) {
                                        $q->where('start_date', '<=', $toDate);
                                    }
                                })
                                ->first();

                            return $plan ? PlanningResource::getUrl('edit', ['record' => $plan]) : null;
                        } else {
                            $evaluation = Evaluation::query()
                                ->whereHas('planning', function ($q) use ($record, $fromDate, $toDate) {
                                    $q->where('student_id', $record->id)
                                      ->where(function ($sq) use ($fromDate, $toDate) {
                                          if ($fromDate) {
                                              $sq->where('end_date', '>=', $fromDate);
                                          }
                                          if ($toDate) {
                                              $sq->where('start_date', '<=', $toDate);
                                          }
                                      });
                                })->first();

                            return $evaluation ? EvaluationResource::getUrl('edit', [
                                'planning' => $evaluation->planning_id,
                                'record' => $evaluation->id,
                            ]) : null;
                        }
                    })
                    ->visible(function (Student $record, $livewire) {
                        $filters = $livewire->tableFilters;
                        $type = $filters['type']['value'] ?? 'planning';
                        $timeRange = $filters['time_range'] ?? [];
                        $fromDate = $timeRange['from_date'] ?? null;
                        $toDate = $timeRange['to_date'] ?? null;

                        if ($type === 'planning') {
                            return Planning::query()
                                ->where('student_id', $record->id)
                                ->where(function ($q) use ($fromDate, $toDate) {
                                    if ($fromDate) {
                                        $q->where('end_date', '>=', $fromDate);
                                    }
                                    if ($toDate) {
                                        $q->where('start_date', '<=', $toDate);
                                    }
                                })
                                ->exists();
                        } else {
                            return Evaluation::query()
                                ->whereHas('planning', function ($q) use ($record, $fromDate, $toDate) {
                                    $q->where('student_id', $record->id)
                                      ->where(function ($sq) use ($fromDate, $toDate) {
                                          if ($fromDate) {
                                              $sq->where('end_date', '>=', $fromDate);
                                          }
                                          if ($toDate) {
                                              $sq->where('start_date', '<=', $toDate);
                                          }
                                      });
                                })->exists();
                        }
                    }),
                Action::make('create_planning')
                    ->label(trans('packages.planning_evaluation::planning.actions.create_plan'))
                    ->icon('heroicon-o-document-plus')
                    ->color('primary')
                    ->url(fn (Student $record) => PlanningResource::getUrl('create', ['student_id' => $record->id]))
                    ->visible(function (Student $record, $livewire) {
                        $filters = $livewire->tableFilters;
                        $type = $filters['type']['value'] ?? 'planning';
                        if ($type !== 'planning') {
                            return false;
                        }
                        $timeRange = $filters['time_range'] ?? [];
                        $fromDate = $timeRange['from_date'] ?? null;
                        $toDate = $timeRange['to_date'] ?? null;

                        return !Planning::query()
                            ->where('student_id', $record->id)
                            ->where(function ($q) use ($fromDate, $toDate) {
                                if ($fromDate) {
                                    $q->where('end_date', '>=', $fromDate);
                                }
                                if ($toDate) {
                                    $q->where('start_date', '<=', $toDate);
                                }
                            })
                            ->exists();
                    }),
                Action::make('create_evaluation')
                    ->label(trans('packages.planning_evaluation::planning.tracker.evaluation'))
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->action(function (Student $record, $livewire) {
                        $filters = $livewire->tableFilters;
                        $timeRange = $filters['time_range'] ?? [];
                        $fromDate = $timeRange['from_date'] ?? null;
                        $toDate = $timeRange['to_date'] ?? null;

                        $plan = Planning::query()
                            ->where('student_id', $record->id)
                            ->where(function ($q) use ($fromDate, $toDate) {
                                if ($fromDate) {
                                    $q->where('end_date', '>=', $fromDate);
                                }
                                if ($toDate) {
                                    $q->where('start_date', '<=', $toDate);
                                }
                            })
                            ->first();

                        if ($plan) {
                            $evaluation = Evaluation::upsertFromPlanning($plan);
                            return redirect(EvaluationResource::getUrl('edit', [
                                'planning' => $plan->id,
                                'record' => $evaluation->id,
                            ]));
                        }
                    })
                    ->visible(function (Student $record, $livewire) {
                        $filters = $livewire->tableFilters;
                        $type = $filters['type']['value'] ?? 'planning';
                        if ($type !== 'evaluation') {
                            return false;
                        }
                        $timeRange = $filters['time_range'] ?? [];
                        $fromDate = $timeRange['from_date'] ?? null;
                        $toDate = $timeRange['to_date'] ?? null;

                        $planExists = Planning::query()
                            ->where('student_id', $record->id)
                            ->where(function ($q) use ($fromDate, $toDate) {
                                if ($fromDate) {
                                    $q->where('end_date', '>=', $fromDate);
                                }
                                if ($toDate) {
                                    $q->where('start_date', '<=', $toDate);
                                }
                            })
                            ->exists();

                        if (!$planExists) {
                            return false;
                        }

                        return !Evaluation::query()
                            ->whereHas('planning', function ($q) use ($record, $fromDate, $toDate) {
                                $q->where('student_id', $record->id)
                                  ->where(function ($sq) use ($fromDate, $toDate) {
                                      if ($fromDate) {
                                          $sq->where('end_date', '>=', $fromDate);
                                      }
                                      if ($toDate) {
                                          $sq->where('start_date', '<=', $toDate);
                                      }
                                  });
                            })->exists();
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label(trans('packages.planning_evaluation::planning.tracker.export_excel'))
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('Bao_cao_nop_KH_DG_' . now()->format('Ymd_His')),
                    ]),
            ]);
    }
}
