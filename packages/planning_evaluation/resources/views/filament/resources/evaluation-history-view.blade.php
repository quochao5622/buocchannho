<div class="space-y-4 p-4 dark:text-gray-200">
    <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
        <div>
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ trans('packages.planning_evaluation::evaluation.fields.name') }}</span>
            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $snapshot['name'] ?? '-' }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ trans('packages.planning_evaluation::evaluation.fields.status') }}</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                {{ $snapshot['status'] ?? '-' }}
            </span>
        </div>
        <div class="col-span-2">
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ trans('packages.planning_evaluation::evaluation.fields.description') }}</span>
            <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $snapshot['description'] ?? trans('packages.planning_evaluation::planning.history.no_data') }}</p>
        </div>
    </div>

    <div>
        <h4 class="text-md font-bold mb-2 text-gray-800 dark:text-white">{{ trans('packages.planning_evaluation::evaluation.fields.evaluation_details') }}</h4>
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 font-semibold uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 w-1/4">{{ trans('packages.planning_evaluation::evaluation.fields.linh_vuc') }}</th>
                        <th class="px-4 py-3 w-1/3">{{ trans('packages.planning_evaluation::evaluation.fields.muc_tieu') }}</th>
                        <th class="px-4 py-3 w-1/6 text-center">{{ trans('packages.planning_evaluation::evaluation.fields.danh_gia') }}</th>
                        <th class="px-4 py-3 w-1/4">{{ trans('packages.planning_evaluation::evaluation.fields.nhan_xet') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($snapshot['evaluation_details'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 align-top font-medium text-gray-900 dark:text-white border-r border-gray-100 dark:border-gray-700">
                                {!! nl2br(e($row['linh_vuc'] ?? '')) !!}
                            </td>
                            <td class="px-4 py-3 align-top colspan-3 p-0">
                                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @forelse($row['muc_tieu'] ?? [] as $goal)
                                            <tr>
                                                <td class="px-4 py-2 align-top w-2/3">
                                                    {{ $goal['content'] ?? '' }}
                                                </td>
                                                <td class="px-4 py-2 align-top w-1/3 text-center border-l border-r border-gray-100 dark:border-gray-700">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold 
                                                        @if(($goal['danh_gia'] ?? '') === '+') bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200
                                                        @elseif(($goal['danh_gia'] ?? '') === '+/-') bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200
                                                        @elseif(($goal['danh_gia'] ?? '') === '-') bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200
                                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                                                        {{ $goal['danh_gia'] ?? trans('packages.planning_evaluation::planning.history.not_evaluated') }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2 align-top w-1/2">
                                                    {{ $goal['nhan_xet'] ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-4 py-2 text-center text-gray-500">{{ trans('packages.planning_evaluation::planning.history.no_goals') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500">{{ trans('packages.planning_evaluation::planning.history.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
