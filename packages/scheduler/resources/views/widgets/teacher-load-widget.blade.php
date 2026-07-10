<div class="space-y-3">
    <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
            {{ trans('packages.scheduler::scheduler.teacher_load.title') }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $periodLabel }}</p>
    </div>

    @if (empty($rows))
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ trans('packages.scheduler::scheduler.teacher_load.empty') }}
        </p>
    @else
        <div class="overflow-x-auto border border-gray-200 rounded-lg dark:border-gray-700">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-200">{{ trans('packages.scheduler::scheduler.teacher_load.teacher') }}</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-200">{{ trans('packages.scheduler::scheduler.teacher_load.sessions') }}</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-200">{{ trans('packages.scheduler::scheduler.teacher_load.hours') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($rows as $row)
                        <tr>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $row['teacher_name'] }}</td>
                            <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-200">{{ $row['sessions'] }}</td>
                            <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-200">{{ $row['hours'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
