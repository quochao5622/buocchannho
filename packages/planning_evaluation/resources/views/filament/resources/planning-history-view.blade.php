<div class="space-y-4 p-4 dark:text-gray-200">
    <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
        <div>
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ trans('packages.planning_evaluation::planning.fields.name') }}</span>
            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $snapshot['name'] ?? '-' }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ trans('packages.planning_evaluation::planning.fields.status') }}</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                {{ $snapshot['status'] ?? '-' }}
            </span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ trans('packages.planning_evaluation::planning.fields.start_date') }}</span>
            <span class="text-sm text-gray-900 dark:text-white">{{ isset($snapshot['start_date']) ? date('d/m/Y', strtotime($snapshot['start_date'])) : '-' }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ trans('packages.planning_evaluation::planning.fields.end_date') }}</span>
            <span class="text-sm text-gray-900 dark:text-white">{{ isset($snapshot['end_date']) ? date('d/m/Y', strtotime($snapshot['end_date'])) : '-' }}</span>
        </div>
        <div class="col-span-2">
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ trans('packages.planning_evaluation::planning.fields.description') }}</span>
            <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $snapshot['description'] ?? trans('packages.planning_evaluation::planning.history.no_data') }}</p>
        </div>
    </div>

    <div>
        <h4 class="text-md font-bold mb-2 text-gray-800 dark:text-white">{{ trans('packages.planning_evaluation::planning.fields.details') }}</h4>
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 font-semibold uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">{{ trans('packages.planning_evaluation::planning.fields.linh_vuc') }}</th>
                        <th class="px-4 py-3">{{ trans('packages.planning_evaluation::planning.fields.muc_tieu') }}</th>
                        <th class="px-4 py-3">{{ trans('packages.planning_evaluation::planning.fields.hoat_dong') }}</th>
                        <th class="px-4 py-3">{{ trans('packages.planning_evaluation::planning.fields.phuong_tien') }}</th>
                        <th class="px-4 py-3">{{ trans('packages.planning_evaluation::planning.fields.muc_tieu_du_phong') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($snapshot['planning_details'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 align-top font-medium text-gray-900 dark:text-white">
                                @foreach($row['linh_vuc'] ?? [] as $item)
                                    <div class="prose prose-sm dark:prose-invert">{!! \Illuminate\Support\Str::markdown($item['content'] ?? '') !!}</div>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 align-top">
                                @foreach($row['muc_tieu'] ?? [] as $item)
                                    <div class="prose prose-sm dark:prose-invert mb-1">{!! \Illuminate\Support\Str::markdown($item['content'] ?? '') !!}</div>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 align-top">
                                @foreach($row['hoat_dong'] ?? [] as $item)
                                    <div class="prose prose-sm dark:prose-invert mb-1">{!! \Illuminate\Support\Str::markdown($item['content'] ?? '') !!}</div>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 align-top">
                                @foreach($row['phuong_tien'] ?? [] as $item)
                                    <div class="prose prose-sm dark:prose-invert mb-1">{!! \Illuminate\Support\Str::markdown($item['content'] ?? '') !!}</div>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 align-top">
                                @foreach($row['muc_tieu_du_phong'] ?? [] as $item)
                                    <div class="prose prose-sm dark:prose-invert mb-1">{!! \Illuminate\Support\Str::markdown($item['content'] ?? '') !!}</div>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">{{ trans('packages.planning_evaluation::planning.history.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
