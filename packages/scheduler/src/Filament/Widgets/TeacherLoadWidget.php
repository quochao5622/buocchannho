<?php

namespace Quochao56\Scheduler\Filament\Widgets;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\Widget;
use Quochao56\Scheduler\Models\Schedule;

class TeacherLoadWidget extends Widget
{
    protected string $view = 'scheduler::widgets.teacher-load-widget';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = now()->endOfWeek(Carbon::SUNDAY);

        $schedules = Schedule::query()
            ->active()
            ->with('employee')
            ->whereDate('start_date', '<=', $weekEnd->toDateString())
            ->where(function ($query) use ($weekStart) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $weekStart->toDateString());
            })
            ->get();

        $loadByTeacher = [];

        foreach ($schedules as $schedule) {
            if (! $schedule->employee) {
                continue;
            }

            $occurrences = $this->countWeeklyOccurrences($schedule, $weekStart, $weekEnd);
            if ($occurrences === 0) {
                continue;
            }

            $hoursPerSession = Carbon::parse($schedule->start_time)->diffInMinutes(Carbon::parse($schedule->end_time)) / 60;
            $teacherId = $schedule->employee_id;

            if (! isset($loadByTeacher[$teacherId])) {
                $loadByTeacher[$teacherId] = [
                    'teacher_name' => $schedule->employee->name,
                    'sessions' => 0,
                    'hours' => 0.0,
                ];
            }

            $loadByTeacher[$teacherId]['sessions'] += $occurrences;
            $loadByTeacher[$teacherId]['hours'] += ($hoursPerSession * $occurrences);
        }

        $rows = collect($loadByTeacher)
            ->sortByDesc('hours')
            ->values()
            ->map(function (array $item) {
                $item['hours'] = number_format($item['hours'], 2);

                return $item;
            })
            ->all();

        return [
            'rows' => $rows,
            'periodLabel' => trans('packages.scheduler::scheduler.teacher_load.period', [
                'start' => $weekStart->format('d/m/Y'),
                'end' => $weekEnd->format('d/m/Y'),
            ]),
        ];
    }

    protected function countWeeklyOccurrences(Schedule $schedule, Carbon $weekStart, Carbon $weekEnd): int
    {
        $effectiveStart = $schedule->start_date?->copy()?->startOfDay();
        $effectiveEnd = $schedule->end_date?->copy()?->endOfDay() ?? $weekEnd->copy();

        if (! $effectiveStart) {
            return 0;
        }

        $rangeStart = $effectiveStart->greaterThan($weekStart) ? $effectiveStart->copy() : $weekStart->copy();
        $rangeEnd = $effectiveEnd->lessThan($weekEnd) ? $effectiveEnd->copy() : $weekEnd->copy();

        if ($rangeStart->gt($rangeEnd)) {
            return 0;
        }

        if ($schedule->day_of_week === null) {
            return ($schedule->start_date && $schedule->start_date->betweenIncluded($rangeStart, $rangeEnd)) ? 1 : 0;
        }

        $days = collect((array) $schedule->day_of_week)
            ->map(fn ($day) => (int) $day)
            ->filter(fn ($day) => $day >= 1 && $day <= 7)
            ->values()
            ->all();

        if (empty($days)) {
            return 0;
        }

        $count = 0;
        $period = CarbonPeriod::create($rangeStart, $rangeEnd);

        foreach ($period as $date) {
            $dayOfWeek = $date->dayOfWeek === 0 ? 1 : ($date->dayOfWeek + 1);
            if (in_array($dayOfWeek, $days, true)) {
                $count++;
            }
        }

        return $count;
    }
}
