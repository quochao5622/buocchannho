<?php

namespace Quochao56\PlanningEvaluation\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Quochao56\PlanningEvaluation\Models\Evaluation;

class StudentProgressChart extends ChartWidget
{
    public ?int $studentId = null;
    public ?string $dateFrom = null;
    public ?string $dateTo   = null;

    public function getHeading(): ?string
    {
        return trans('packages.planning_evaluation::planning.progress.chart_title');
    }

    protected function getData(): array
    {
        if (!$this->studentId) {
            return ['datasets' => [], 'labels' => []];
        }

        $query = Evaluation::query()
            ->whereHas('planning', function ($q) {
                $q->where('student_id', $this->studentId);
            })
            ->orderBy('created_at', 'asc');

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $evaluations = $query->get();

        $labels         = [];
        $achievedData   = [];
        $partialData    = [];
        $notAchievedData = [];

        foreach ($evaluations as $evaluation) {
            $totalGoals  = 0;
            $achieved    = 0;
            $partial     = 0;
            $notAchieved = 0;

            $details = $evaluation->evaluation_details ?? [];
            foreach ($details as $row) {
                $goals = $row['muc_tieu'] ?? [];
                foreach ($goals as $goal) {
                    $totalGoals++;
                    $rating = $goal['danh_gia'] ?? '';
                    if ($rating === '+') {
                        $achieved++;
                    } elseif ($rating === '+/-') {
                        $partial++;
                    } elseif ($rating === '-') {
                        $notAchieved++;
                    }
                }
            }

            if ($totalGoals > 0) {
                $labels[]          = $evaluation->created_at->format('d/m/Y') . ' (' . ($evaluation->planning?->name ?? 'KH') . ')';
                $achievedData[]    = round(($achieved / $totalGoals) * 100);
                $partialData[]     = round(($partial / $totalGoals) * 100);
                $notAchievedData[] = round(($notAchieved / $totalGoals) * 100);
            }
        }

        return [
            'datasets' => [
                [
                    'label'           => trans('packages.planning_evaluation::planning.progress.achieved'),
                    'data'            => $achievedData,
                    'borderColor'     => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill'            => true,
                ],
                [
                    'label'           => trans('packages.planning_evaluation::planning.progress.partial'),
                    'data'            => $partialData,
                    'borderColor'     => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill'            => true,
                ],
                [
                    'label'           => trans('packages.planning_evaluation::planning.progress.not_achieved'),
                    'data'            => $notAchievedData,
                    'borderColor'     => '#EF4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill'            => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
