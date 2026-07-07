<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
            @livewire(\Quochao56\Scheduler\Filament\Widgets\TeacherLoadWidget::class)
        </div>

        {{-- Card bộ lọc --}}
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <form class="grid grid-cols-1 gap-4 md:grid-cols-4">
                {{ $this->form }}
            </form>
        </div>

        {{-- Lịch FullCalendar --}}
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
            @livewire(\Quochao56\Scheduler\Filament\Widgets\CalendarWidget::class, [
                'employeeId' => $employeeId,
                'studentId' => $studentId,
                'classroomId' => $classroomId,
                'guardianKeyword' => $guardianKeyword,
            ], 'calendar-widget-' . $employeeId . '-' . $studentId . '-' . $classroomId . '-' . $guardianKeyword)
        </div>
    </div>
</x-filament-panels::page>
