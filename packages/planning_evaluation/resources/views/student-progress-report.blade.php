<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
        <form class="space-y-4">
            {{ $this->form }}
        </form>
    </div>

    @if ($this->studentId)
        @php
            $hasEvaluations = \Quochao56\PlanningEvaluation\Models\Evaluation::query()
                ->whereHas('planning', function ($q) {
                    $q->where('student_id', $this->studentId);
                })
                ->exists();
        @endphp

        @if ($hasEvaluations)
            <div class="mt-6 space-y-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                    {{ trans('packages.planning_evaluation::planning.progress.chart_heading', ['name' => \Quochao56\Student\Models\Student::find($this->studentId)?->name]) }}
                </h3>
                
                @livewire(\Quochao56\PlanningEvaluation\Filament\Widgets\StudentProgressChart::class, ['studentId' => $this->studentId], key('progress-chart-' . $this->studentId))
            </div>
        @else
            <div class="mt-6 p-6 bg-amber-50 dark:bg-amber-950/30 rounded-lg border border-amber-200 dark:border-amber-900 text-amber-800 dark:text-amber-300 flex items-center gap-3">
                <svg class="h-6 w-6 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <span class="font-medium text-sm">
                    {{ trans('packages.planning_evaluation::planning.progress.no_evaluations') }}
                </span>
            </div>
        @endif
    @else
        <div class="mt-6 p-8 bg-gray-50 dark:bg-gray-900 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 text-center text-gray-500 dark:text-gray-400">
            {{ trans('packages.planning_evaluation::planning.progress.empty_state') }}
        </div>
    @endif
</x-filament-panels::page>
