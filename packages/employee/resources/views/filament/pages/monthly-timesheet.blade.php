<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Bộ lọc -->
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <form>
                {{ $this->form }}
            </form>
        </div>

        <!-- Bảng ma trận công -->
        <style>
            .timesheet-table .timesheet-cell {
                position: relative !important;
            }
            .timesheet-table .edit-icon-container {
                position: absolute !important;
                top: 3px !important;
                right: 3px !important;
                display: none !important;
                z-index: 10 !important;
                background-color: rgba(255, 255, 255, 0.95);
                border-radius: 9999px;
                padding: 2px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            .dark .timesheet-table .edit-icon-container {
                background-color: rgba(31, 41, 55, 0.95);
            }
            .timesheet-table .timesheet-cell:hover .edit-icon-container {
                display: block !important;
            }
            .timesheet-table .today-column {
                background-color: rgba(59, 130, 246, 0.04) !important;
            }
            .timesheet-table .weekend-column {
                background-color: rgba(249, 115, 22, 0.07) !important;
            }
            .dark .timesheet-table .weekend-column {
                background-color: rgba(249, 115, 22, 0.08) !important;
            }

            /* Styles cho cột tổng giờ công */
            .total-kpi-good {
                color: #047857 !important;
                background-color: #ecfdf5 !important;
            }
            .dark .total-kpi-good {
                color: #34d399 !important;
                background-color: rgba(16, 185, 129, 0.15) !important;
            }
            .total-kpi-good .kpi-dot {
                background-color: #10b981 !important;
            }

            .total-kpi-warning {
                color: #b45309 !important;
                background-color: #fffbeb !important;
            }
            .dark .total-kpi-warning {
                color: #fbbf24 !important;
                background-color: rgba(245, 158, 11, 0.15) !important;
            }
            .total-kpi-warning .kpi-dot {
                background-color: #f59e0b !important;
            }

            .total-kpi-danger {
                color: #be123c !important;
                background-color: #fff1f2 !important;
            }
            .dark .total-kpi-danger {
                color: #fb7185 !important;
                background-color: rgba(244, 63, 94, 0.15) !important;
            }
            .total-kpi-danger .kpi-dot {
                background-color: #f43f5e !important;
            }
        </style>
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <div class="overflow-x-auto relative rounded-xl border border-gray-100 dark:border-gray-800 max-h-[600px] overflow-y-auto">
                <table class="w-full text-sm text-left border-separate timesheet-table" style="border-spacing: 0;">
                    <thead>
                        <tr class="sticky top-0 z-20 text-gray-700 dark:text-gray-300">
                            <!-- Cột cố định tên nhân viên -->
                            <th class="p-4 font-semibold text-center border-b border-r border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 sticky left-0 z-30 shadow-[2px_0_5px_rgba(0,0,0,0.01)]" style="min-width: 260px; width: 260px;">
                                Giáo viên / Nhân viên
                            </th>
                            <!-- Cột cố định chức vụ -->
                            <th class="p-4 font-semibold text-center border-b border-r border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 sticky z-30 shadow-[2px_0_5px_rgba(0,0,0,0.02)]" style="left: 260px; min-width: 160px; width: 160px;">
                                Chức vụ
                            </th>
                            <!-- Các cột ngày -->
                            @foreach ($dates as $date)
                                @php
                                    $headerClass = $date['is_today'] 
                                        ? 'bg-primary-50 dark:bg-primary-950/30 text-primary-600 font-bold border-x border-primary-200 dark:border-primary-800' 
                                        : ($date['is_weekend'] 
                                            ? 'bg-orange-100/60 dark:bg-orange-950/30 text-orange-600' 
                                            : 'bg-gray-50 dark:bg-gray-800');
                                    
                                    $columnClass = $date['is_today'] ? 'today-column' : ($date['is_weekend'] ? 'weekend-column' : '');
                                @endphp
                                <th class="p-3 font-semibold text-center min-w-[70px] border-b border-r border-gray-100 dark:border-gray-800 {{ $headerClass }} {{ $columnClass }}">
                                    <div class="text-xs">{{ $date['formatted'] }}</div>
                                    <div class="text-[10px] font-semibold mt-0.5 {{ $date['is_weekend'] ? 'text-orange-500' : 'text-gray-400 dark:text-gray-500' }}">
                                        {{ $date['day_name'] }}
                                    </div>
                                </th>
                            @endforeach
                            <!-- Cột tổng cộng -->
                            <th class="p-4 font-semibold text-center min-w-[90px] border-b border-gray-100 dark:border-gray-800 bg-primary-50/50 dark:bg-primary-950/20 text-primary-600 sticky right-0 z-10 shadow-[-2px_0_5px_rgba(0,0,0,0.02)]">
                                Tổng cộng
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900">
                        @forelse ($matrix as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                <!-- Tên nhân viên (Sticky) -->
                                <td class="p-4 font-medium text-gray-950 dark:text-white border-b border-r border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 sticky left-0 z-10 shadow-[2px_0_5px_rgba(0,0,0,0.01)]" style="min-width: 260px; width: 260px;">
                                    {{ $row['employee_name'] }}
                                </td>

                                <!-- Chức vụ (Sticky) -->
                                <td class="p-4 text-center text-gray-600 dark:text-gray-400 border-b border-r border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 sticky z-10 shadow-[2px_0_5px_rgba(0,0,0,0.02)]" style="left: 260px; min-width: 160px; width: 160px;">
                                    {{ $row['position'] }}
                                </td>

                                <!-- Các ô giờ công -->
                                @foreach ($dates as $date)
                                    @php
                                        $dayData = $row['days'][$date['db']] ?? ['hours' => null, 'status' => 'none', 'details' => []];
                                        $hours = $dayData['hours'];
                                        $status = $dayData['status'];
                                        $canEdit = auth()->user()?->isSuperAdmin() || auth()->user()?->hasPermissionTo('employee_attendances.edit');
                                        
                                        $cellBg = 'bg-white dark:bg-gray-900';
                                        $badgeClass = '';
                                        $badgeText = '';
                                        
                                        if ($status === 'on_leave') {
                                            $cellBg = 'bg-purple-50/40 dark:bg-purple-950/15';
                                            $badgeClass = 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-400 border border-purple-200 dark:border-purple-800';
                                            $badgeText = 'P';
                                        } elseif ($status === 'absent') {
                                            $cellBg = 'bg-rose-50/40 dark:bg-rose-950/15';
                                            $badgeClass = 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-400 border border-rose-200 dark:border-rose-800';
                                            $badgeText = 'V';
                                        } elseif ($hours > 0) {
                                            if ($hours < 4.0) {
                                                $cellBg = 'bg-rose-50/30 dark:bg-rose-950/10';
                                                $badgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-400 border border-rose-200 dark:border-rose-800';
                                                $badgeText = number_format($hours, 1);
                                            } elseif ($hours < 8.0) {
                                                $cellBg = 'bg-amber-50/30 dark:bg-amber-950/10';
                                                $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400 border border-amber-200 dark:border-amber-800';
                                                $badgeText = number_format($hours, 1);
                                            } elseif ($hours == 8.0) {
                                                $cellBg = 'bg-emerald-50/30 dark:bg-emerald-950/10';
                                                $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800';
                                                $badgeText = number_format($hours, 1);
                                            } else {
                                                $cellBg = 'bg-sky-50/30 dark:bg-sky-950/10';
                                                $badgeClass = 'bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-400 border border-sky-200 dark:border-sky-800';
                                                $badgeText = '+' . number_format($hours, 1);
                                            }
                                        } else {
                                            if ($date['is_today']) {
                                                $cellBg = 'bg-primary-50/10 dark:bg-primary-950/5';
                                            } elseif ($date['is_weekend']) {
                                                $cellBg = 'bg-gray-100/55 dark:bg-gray-800/35';
                                            }
                                        }
                                        
                                        $todayClass = $date['is_today'] ? 'ring-1 ring-primary-500/30 ring-inset bg-primary-50/10 dark:bg-primary-950/5' : '';
                                        $columnClass = $date['is_today'] ? 'today-column' : ($date['is_weekend'] ? 'weekend-column' : '');
                                    @endphp
                                    <td 
                                        @if ($canEdit)
                                            wire:click="mountAction('editAttendanceAction', { employeeId: {{ $row['employee_id'] }}, date: '{{ $date['db'] }}' })"
                                            class="p-3 text-center border-b border-r border-gray-100 dark:border-gray-800 transition cursor-pointer hover:bg-primary-50/70 dark:hover:bg-primary-950/30 timesheet-cell {{ $cellBg }} {{ $todayClass }} {{ $columnClass }}"
                                        @else
                                            class="p-3 text-center border-b border-r border-gray-100 dark:border-gray-800 transition timesheet-cell {{ $cellBg }} {{ $todayClass }} {{ $columnClass }}"
                                        @endif
                                    >
                                        @if ($badgeText !== '')
                                            <span class="inline-flex items-center justify-center px-2 py-1 rounded-lg {{ $badgeClass }} font-bold text-xs shadow-sm tooltip cursor-help" title="{{ $status === 'on_leave' ? 'Nghỉ phép' : ($status === 'absent' ? 'Vắng mặt' : ($hours < 4.0 ? 'Làm dưới 4h (Quá thiếu giờ)' : ($hours < 8.0 ? 'Làm dưới 8h (Thiếu giờ)' : ($hours == 8.0 ? 'Làm đủ 8h' : 'Làm trên 8h (Tăng ca)')))) }}">
                                                {{ $badgeText }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-700 font-normal">-</span>
                                        @endif

                                        @if ($canEdit)
                                            <span class="edit-icon-container text-primary-500 dark:text-primary-400">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </span>
                                        @endif
                                    </td>
                                @endforeach

                                <!-- Tổng giờ công (Sticky) -->
                                @php
                                    $totalHours = $row['total_hours'];
                                    if ($totalHours >= 160.0) {
                                        $kpiClass = 'total-kpi-good';
                                        $titleText = 'Đủ giờ công theo quy định (>= 160h)';
                                    } elseif ($totalHours >= 140.0) {
                                        $kpiClass = 'total-kpi-warning';
                                        $titleText = 'Gần đủ giờ công (140h - 159h)';
                                    } else {
                                        $kpiClass = 'total-kpi-danger';
                                        $titleText = 'Thiếu giờ công (< 140h)';
                                    }
                                @endphp
                                <td class="p-4 text-center font-bold border-b border-gray-100 dark:border-gray-800 {{ $kpiClass }} sticky right-0 z-10 shadow-[-2px_0_5px_rgba(0,0,0,0.02)]">
                                    <div class="inline-flex items-center justify-center gap-1.5 tooltip cursor-help" title="{{ $titleText }}">
                                        <span class="w-2.5 h-2.5 rounded-full kpi-dot animate-pulse"></span>
                                        <span>{{ number_format($totalHours, 1) }}h</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($dates) + 3 }}" class="p-8 text-center text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                                    Không có dữ liệu nhân viên.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Chú thích ký hiệu -->
            <div style="margin-top: 1.5rem; display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: center; border-top: 1px solid #f3f4f6; padding-top: 1rem;" class="text-xs text-gray-500 dark:text-gray-400 dark:border-gray-800">
                <span class="font-semibold text-gray-700 dark:text-gray-300">Chú thích ký hiệu:</span>
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;">
                    <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-400 border border-purple-200 dark:border-purple-800 font-bold text-[10px] shadow-sm">P</span> Nghỉ phép
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;">
                    <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-400 border border-rose-200 dark:border-rose-800 font-bold text-[10px] shadow-sm">V</span> Vắng mặt
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;">
                    <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-400 border border-rose-200 dark:border-rose-800 font-bold text-[10px] shadow-sm">&lt; 4.0</span> Làm dưới 4.0h (Quá thiếu giờ)
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;">
                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400 border border-amber-200 dark:border-amber-800 font-bold text-[10px] shadow-sm">4.0 - 7.9</span> Làm dưới 8.0h (Chưa đủ giờ)
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;">
                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-bold text-[10px] shadow-sm">8.0</span> Làm đủ 8.0h
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;">
                    <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-400 border border-sky-200 dark:border-sky-800 font-bold text-[10px] shadow-sm">+8.0</span> Làm trên 8.0h (Tăng ca/OT)
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;">
                    <span class="inline-block w-2.5 h-2.5 rounded-full mr-0.5" style="background-color: #10b981;"></span> Tổng giờ &gt;= 160h (Đủ giờ công)
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;">
                    <span class="inline-block w-2.5 h-2.5 rounded-full mr-0.5" style="background-color: #f59e0b;"></span> Tổng giờ 140h - 159h (Gần đủ)
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;">
                    <span class="inline-block w-2.5 h-2.5 rounded-full mr-0.5" style="background-color: #f43f5e;"></span> Tổng giờ &lt; 140h (Thiếu giờ)
                </span>
            </div>
        </div>
    </div>
</x-filament-panels::page>
