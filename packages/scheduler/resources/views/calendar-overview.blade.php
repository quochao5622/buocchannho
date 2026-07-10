<x-filament-panels::page>
    <div class="space-y-6">
        @if(auth()->user() && auth()->user()->can('employees.index'))
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
            @livewire(\Quochao56\Scheduler\Filament\Widgets\TeacherLoadWidget::class)
        </div>
        @endif

        {{-- Card bộ lọc --}}
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <form>
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

    <style>
        /* Keep pre-line for title to wrap texts */
        .fc-v-event .fc-event-title-container,
        .fc-event-title,
        .fc-event-title-container {
            white-space: pre-line !important;
            font-size: 0.775rem !important;
            line-height: 1.25 !important;
        }

        /* Adjust slot height so 30m slots have more height, and 1h slots are tall enough */
        .fc .fc-timegrid-slot {
            height: 3.5rem !important;
        }

        /* Event style overrides */
        .fc-event {
            border-width: 0 0 0 4px !important;
            border-radius: 6px !important;
            padding: 4px 6px !important;
            font-weight: 500 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03) !important;
        }

        .fc-event-main, .fc-event-time, .fc-event-title {
            color: inherit !important;
        }

        /* --- LIGHT MODE EVENT COLORS --- */
        .event-status-normal {
            background-color: rgba(16, 185, 129, 0.12) !important;
            border-color: #10b981 !important;
            color: #065f46 !important;
        }
        .event-status-canceled {
            background-color: rgba(239, 68, 68, 0.12) !important;
            border-color: #ef4444 !important;
            color: #991b1b !important;
        }
        .event-status-substituted {
            background-color: rgba(99, 102, 241, 0.12) !important;
            border-color: #6366f1 !important;
            color: #3730a3 !important;
        }
        .event-status-rescheduled {
            background-color: rgba(245, 158, 11, 0.12) !important;
            border-color: #f59e0b !important;
            color: #92400e !important;
        }
        .event-status-absent {
            background-color: rgba(156, 163, 175, 0.12) !important;
            border-color: #9ca3af !important;
            color: #374151 !important;
        }
        .event-status-makeup {
            background-color: rgba(168, 85, 247, 0.12) !important;
            border-color: #a855f7 !important;
            color: #6b21a8 !important;
        }
        .event-status-group {
            background-color: rgba(59, 130, 246, 0.12) !important;
            border-color: #3b82f6 !important;
            color: #1e40af !important;
        }

        /* --- DARK MODE EVENT COLORS --- */
        .dark .event-status-normal {
            background-color: rgba(16, 185, 129, 0.2) !important;
            border-color: #34d399 !important;
            color: #a7f3d0 !important;
        }
        .dark .event-status-canceled {
            background-color: rgba(239, 68, 68, 0.2) !important;
            border-color: #f87171 !important;
            color: #fecaca !important;
        }
        .dark .event-status-substituted {
            background-color: rgba(99, 102, 241, 0.2) !important;
            border-color: #818cf8 !important;
            color: #e0e7ff !important;
        }
        .dark .event-status-rescheduled {
            background-color: rgba(245, 158, 11, 0.2) !important;
            border-color: #fbbf24 !important;
            color: #fef3c7 !important;
        }
        .dark .event-status-absent {
            background-color: rgba(156, 163, 175, 0.2) !important;
            border-color: #9ca3af !important;
            color: #e5e7eb !important;
        }
        .dark .event-status-makeup {
            background-color: rgba(168, 85, 247, 0.2) !important;
            border-color: #c084fc !important;
            color: #f3e8ff !important;
        }
        .dark .event-status-group {
            background-color: rgba(59, 130, 246, 0.2) !important;
            border-color: #60a5fa !important;
            color: #dbeafe !important;
        }

        /* Customize FullCalendar buttons to look like native Filament Light Theme buttons */
        .fc .fc-button-primary {
            background-color: #ffffff !important;
            border-color: #e5e7eb !important;
            color: #374151 !important;
            font-weight: 500 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            text-transform: none !important;
            padding: 0.4rem 0.8rem !important;
            font-size: 0.825rem !important;
            border-radius: 0.375rem !important;
            transition: all 0.15s ease-in-out !important;
        }

        .fc .fc-button-primary:hover {
            background-color: #f9fafb !important;
            color: #111827 !important;
            border-color: #d1d5db !important;
        }

        .fc .fc-button-primary:focus, 
        .fc .fc-button-primary:active {
            background-color: #f3f4f6 !important;
            border-color: #c5c7cb !important;
            color: #111827 !important;
            box-shadow: none !important;
        }

        .fc .fc-button-active {
            background-color: #f3f4f6 !important;
            border-color: #c5c7cb !important;
            color: #111827 !important;
            font-weight: 600 !important;
        }

        /* Customize FullCalendar buttons for Dark Theme */
        .dark .fc .fc-button-primary {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
            color: #d1d5db !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.5) !important;
        }

        .dark .fc .fc-button-primary:hover {
            background-color: #374151 !important;
            color: #ffffff !important;
            border-color: #4b5563 !important;
        }

        .dark .fc .fc-button-primary:focus, 
        .dark .fc .fc-button-primary:active {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
            color: #ffffff !important;
            box-shadow: none !important;
        }

        .dark .fc .fc-button-active {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }

        /* Correct button group spacing */
        .fc-button-group > .fc-button:not(:first-child) {
            margin-left: -1px !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }
        .fc-button-group > .fc-button:not(:last-child) {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        /* Sleek toolbar title */
        .fc .fc-toolbar-title {
            font-size: 1.125rem !important;
            font-weight: 600 !important;
            color: #111827 !important;
        }
        .dark .fc .fc-toolbar-title {
            color: #f9fafb !important;
        }

        /* Light column headers styling */
        .fc .fc-col-header-cell {
            background-color: #f9fafb !important;
            padding: 6px 0 !important;
            border-color: #e5e7eb !important;
        }
        .fc .fc-col-header-cell-cushion {
            color: #374151 !important;
            font-weight: 600 !important;
            font-size: 0.825rem !important;
            text-decoration: none !important;
        }
        .dark .fc .fc-col-header-cell {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
        }
        .dark .fc .fc-col-header-cell-cushion {
            color: #f9fafb !important;
        }

        /* Time labels customization */
        .fc .fc-timegrid-slot-label-cushion {
            color: #4b5563 !important;
            font-size: 0.775rem !important;
            font-weight: 500 !important;
        }
        .dark .fc .fc-timegrid-slot-label-cushion {
            color: #9ca3af !important;
        }

        /* Dark mode grid borders */
        .dark .fc td, .dark .fc th {
            border-color: #374151 !important;
        }
    </style>
</x-filament-panels::page>
