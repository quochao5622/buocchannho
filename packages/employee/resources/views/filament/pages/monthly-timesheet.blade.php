<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Bộ lọc -->
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <form>
                {{ $this->form }}
            </form>
        </div>

        <!-- Bảng ma trận công -->
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <div class="overflow-x-auto relative rounded-xl border border-gray-100 dark:border-gray-800 max-h-[600px] overflow-y-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-800 sticky top-0 z-20">
                            <!-- Cột cố định tên nhân viên -->
                            <th class="p-4 font-semibold text-center border-r border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 sticky left-0 z-30 shadow-[2px_0_5px_rgba(0,0,0,0.01)]" style="min-width: 260px; width: 260px;">
                                Giáo viên / Nhân viên
                            </th>
                            <!-- Cột cố định chức vụ -->
                            <th class="p-4 font-semibold text-center border-r border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 sticky z-30 shadow-[2px_0_5px_rgba(0,0,0,0.02)]" style="left: 260px; min-width: 160px; width: 160px;">
                                Chức vụ
                            </th>
                            <!-- Các cột ngày -->
                            @foreach ($dates as $date)
                                <th class="p-3 font-semibold text-center min-w-[70px] border-r border-gray-100 dark:border-gray-700 {{ $date['is_weekend'] ? 'bg-orange-50/50 dark:bg-orange-950/20 text-orange-600' : '' }}">
                                    <div>{{ $date['formatted'] }}</div>
                                </th>
                            @endforeach
                            <!-- Cột tổng cộng -->
                            <th class="p-4 font-semibold text-center min-w-[90px] bg-primary-50/50 dark:bg-primary-950/20 text-primary-600 sticky right-0 z-10 shadow-[-2px_0_5px_rgba(0,0,0,0.02)]">
                                Tổng cộng
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($matrix as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                <!-- Tên nhân viên (Sticky) -->
                                <td class="p-4 font-medium text-gray-950 dark:text-white border-r border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 sticky left-0 z-10 shadow-[2px_0_5px_rgba(0,0,0,0.01)]" style="min-width: 260px; width: 260px;">
                                    {{ $row['employee_name'] }}
                                </td>

                                <!-- Chức vụ (Sticky) -->
                                <td class="p-4 text-center text-gray-600 dark:text-gray-400 border-r border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 sticky z-10 shadow-[2px_0_5px_rgba(0,0,0,0.02)]" style="left: 260px; min-width: 160px; width: 160px;">
                                    {{ $row['position'] }}
                                </td>

                                <!-- Các ô giờ công -->
                                @foreach ($dates as $date)
                                    @php
                                        $dayData = $row['days'][$date['db']] ?? ['hours' => null, 'status' => 'none'];
                                        $hours = $dayData['hours'];
                                        $status = $dayData['status'];
                                    @endphp
                                    <td class="p-3 text-center border-r border-gray-100 dark:border-gray-700 transition {{ $date['is_weekend'] ? 'bg-orange-50/10 dark:bg-orange-950/5' : '' }}">
                                        @if ($status === 'on_leave')
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 font-bold text-xs shadow-sm tooltip cursor-help" title="Nghỉ phép">
                                                P
                                            </span>
                                        @elseif ($status === 'absent')
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 font-bold text-xs shadow-sm tooltip cursor-help" title="Vắng mặt">
                                                V
                                            </span>
                                        @elseif ($hours > 0)
                                            <span class="inline-flex items-center justify-center px-2 py-1 rounded-lg {{ $status === 'late' ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400 border border-yellow-200' : 'bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400 border border-green-200' }} font-bold text-xs shadow-sm">
                                                {{ number_format($hours, 1) }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-700 font-normal">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                <!-- Tổng giờ công (Sticky) -->
                                <td class="p-4 text-center font-bold text-primary-600 dark:text-primary-400 bg-primary-50/30 dark:bg-primary-950/10 border-l border-gray-100 dark:border-gray-700 sticky right-0 z-10 shadow-[-2px_0_5px_rgba(0,0,0,0.02)]">
                                    {{ number_format($row['total_hours'], 1) }}h
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($dates) + 3 }}" class="p-8 text-center text-gray-500 dark:text-gray-400">
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
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;"><span class="w-5 h-5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 font-bold text-[10px] inline-flex items-center justify-center shadow-sm">P</span> Nghỉ phép</span>
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;"><span class="w-5 h-5 rounded-full bg-red-100 dark:bg-red-950/40 text-red-600 font-bold text-[10px] inline-flex items-center justify-center shadow-sm">V</span> Vắng mặt</span>
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;"><span class="px-1.5 py-0.5 rounded bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400 font-bold text-[10px] shadow-sm border border-green-200">X.X</span> Số giờ làm việc (Đúng giờ)</span>
                <span style="display: inline-flex; align-items: center; gap: 0.375rem;"><span class="px-1.5 py-0.5 rounded bg-yellow-50 text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400 font-bold text-[10px] shadow-sm border border-yellow-200">X.X</span> Số giờ làm việc (Đi muộn)</span>
            </div>
        </div>
    </div>
</x-filament-panels::page>
