<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <form>
                {{ $this->form }}
            </form>
        </div>
        <div class="mt-5">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:24px;">
            <div class="p-4 bg-white border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-800" style="padding:12px;border:1px solid rgba(59,130,246,.35);border-radius:12px;background:linear-gradient(180deg, rgba(59,130,246,.2), rgba(59,130,246,.05));">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ trans('packages.scheduler::scheduler.daily_operations.summary.total') }}</p>
                <p class="mt-1 text-2xl font-semibold text-blue-600" style="color:#2563eb;font-weight:700;">{{ $summary['total'] }}</p>
            </div>
            <div class="p-4 bg-white border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-800" style="padding:12px;border:1px solid rgba(244,63,94,.35);border-radius:12px;background:linear-gradient(180deg, rgba(244,63,94,.2), rgba(244,63,94,.05));">
                <p class="text-sm text-gray-500 dark:text-gray-400">Đã hủy</p>
                <p class="mt-1 text-2xl font-semibold text-rose-600" style="color:#f43f5e;font-weight:700;">{{ $summary['canceled'] }}</p>
            </div>
            <div class="p-4 bg-white border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-800" style="padding:12px;border:1px solid rgba(245,158,11,.35);border-radius:12px;background:linear-gradient(180deg, rgba(245,158,11,.2), rgba(245,158,11,.05));">
                <p class="text-sm text-gray-500 dark:text-gray-400">Dời lịch</p>
                <p class="mt-1 text-2xl font-semibold text-amber-600" style="color:#f59e0b;font-weight:700;">{{ $summary['rescheduled'] }}</p>
            </div>
        </div>

        <div class="overflow-hidden bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm" style="width:100%;border-collapse:separate;border-spacing:0;">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium" style="padding:10px 12px;white-space:nowrap;">{{ trans('packages.scheduler::scheduler.daily_operations.table.time') }}</th>
                            <th class="px-3 py-2 text-left font-medium" style="padding:10px 12px;white-space:nowrap;">{{ trans('packages.scheduler::scheduler.daily_operations.table.title') }}</th>
                            <th class="px-3 py-2 text-left font-medium" style="padding:10px 12px;white-space:nowrap;">{{ trans('packages.scheduler::scheduler.daily_operations.table.student') }}</th>
                            <th class="px-3 py-2 text-left font-medium" style="padding:10px 12px;white-space:nowrap;">{{ trans('packages.scheduler::scheduler.daily_operations.table.teacher') }}</th>
                            <th class="px-3 py-2 text-left font-medium" style="padding:10px 12px;white-space:nowrap;">{{ trans('packages.scheduler::scheduler.daily_operations.table.classroom') }}</th>
                            <th class="px-3 py-2 text-left font-medium" style="padding:10px 12px;white-space:nowrap;">{{ trans('packages.scheduler::scheduler.daily_operations.table.schedule_status') }}</th>
                            <th class="px-3 py-2 text-left font-medium" style="padding:10px 12px;white-space:nowrap;">{{ trans('packages.scheduler::scheduler.daily_operations.table.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($rows as $row)
                            @php
                                $scheduleStatus = $row['schedule_status'];

                                $scheduleBadge = match ($scheduleStatus) {
                                    'canceled' => ['bg' => 'rgba(244,63,94,.18)', 'text' => '#fb7185', 'border' => 'rgba(244,63,94,.45)'],
                                    'substituted' => ['bg' => 'rgba(99,102,241,.18)', 'text' => '#a5b4fc', 'border' => 'rgba(99,102,241,.45)'],
                                    'rescheduled' => ['bg' => 'rgba(245,158,11,.18)', 'text' => '#fbbf24', 'border' => 'rgba(245,158,11,.45)'],
                                    default => ['bg' => 'rgba(16,185,129,.18)', 'text' => '#34d399', 'border' => 'rgba(16,185,129,.45)'],
                                };
                            @endphp
                            <tr>
                                <td class="px-3 py-2" style="padding:10px 12px;white-space:nowrap;">{{ $row['time'] }}</td>
                                <td class="px-3 py-2" style="padding:10px 12px;min-width:220px;">{{ $row['title'] }}</td>
                                <td class="px-3 py-2" style="padding:10px 12px;white-space:nowrap;">{{ $row['student'] }}</td>
                                <td class="px-3 py-2" style="padding:10px 12px;white-space:nowrap;">{{ $row['teacher'] }}</td>
                                <td class="px-3 py-2" style="padding:10px 12px;white-space:nowrap;">{{ $row['classroom'] }}</td>
                                <td class="px-3 py-2" style="padding:10px 12px;white-space:nowrap;">
                                    <span style="display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid {{ $scheduleBadge['border'] }};background:{{ $scheduleBadge['bg'] }};color:{{ $scheduleBadge['text'] }};font-weight:600;">
                                        {{ trans('packages.scheduler::scheduler.daily_operations.status.' . $row['schedule_status']) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2" style="padding:10px 12px;min-width:180px;">{{ $row['notes'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400" style="padding:18px 12px;">
                                    Chưa có buổi học trong ngày được chọn.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
</x-filament-panels::page>
