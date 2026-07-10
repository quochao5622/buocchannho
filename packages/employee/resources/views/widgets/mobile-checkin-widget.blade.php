<div
    class="fi-wi-mobile-checkin w-full"
    x-data="{
        latitude: null,
        longitude: null,
        geoError: null,
        geoLoading: false,
        getLocation() {
            if (!navigator.geolocation) {
                this.geoError = '{{ trans('packages.employee::employee_attendance.actions.geolocation_required') }}';
                return;
            }

            this.geoLoading = true;

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.latitude = pos.coords.latitude;
                    this.longitude = pos.coords.longitude;
                    this.geoLoading = false;
                    $wire.set('latitude', this.latitude);
                    $wire.set('longitude', this.longitude);
                    $wire.set('geoReady', true);
                },
                () => {
                    this.geoError = '{{ trans('packages.employee::employee_attendance.actions.geolocation_required') }}';
                    this.geoLoading = false;
                    $wire.set('geoReady', false);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }
    }"
    x-init="getLocation()"
>
    @if (!$employee)
        <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-sm text-warning-700 dark:border-warning-700 dark:bg-warning-900/30 dark:text-warning-300">
            {{ trans('packages.employee::employee_attendance.actions.no_associated_employee') }}
        </div>
    @else
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">

            {{-- Header --}}
            <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100/50 px-5 py-4 dark:border-gray-700/50 dark:from-gray-900 dark:to-gray-950">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                            {{ trans('packages.employee::employee_attendance.navigation_label') }}
                        </p>
                        <h2 class="mt-1 truncate text-lg font-bold text-gray-900 dark:text-white">
                            {{ $employee->name }}
                        </h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            {{ now()->isoFormat('dddd, D [tháng] M, Y') }}
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Giờ hiện tại</p>
                        <span
                            class="text-lg font-semibold tabular-nums text-gray-900 dark:text-white"
                            x-data="{ time: '' }"
                            x-init="setInterval(() => { time = new Date().toLocaleTimeString('vi-VN', { hour:'2-digit', minute:'2-digit', second:'2-digit' }) }, 1000); time = new Date().toLocaleTimeString('vi-VN', { hour:'2-digit', minute:'2-digit', second:'2-digit' })"
                            x-text="time"
                        ></span>
                    </div>
                </div>
            </div>

            {{-- Status bar --}}
            <div class="border-b border-gray-100 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-800/80">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2">Trạng thái chấm công hôm nay</p>
                <div class="flex flex-wrap items-center gap-3">
                    @php
                        $sessions = ['morning' => 'Sáng', 'afternoon' => 'Chiều', 'evening' => 'Tối'];
                    @endphp
                    @foreach ($sessions as $key => $label)
                        @php
                            $rec = $todayRecords->firstWhere('session', $key);
                        @endphp
                        <div class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs dark:border-gray-700 dark:bg-gray-900 shadow-sm">
                            <span class="font-medium text-gray-500 dark:text-gray-400">{{ $label }}:</span>
                            @if ($rec)
                                <span class="text-success-600 dark:text-success-400 font-semibold flex items-center gap-1">
                                    {{ $rec->check_in_at?->format('H:i') }}
                                    @if ($rec->check_out_at)
                                        - {{ $rec->check_out_at->format('H:i') }}
                                        <span class="text-[10px] font-normal text-gray-400 dark:text-gray-500">({{ $rec->total_hours }} giờ công)</span>
                                    @else
                                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-success-500 animate-pulse"></span>
                                    @endif
                                </span>
                            @else
                                <span class="text-gray-400 dark:text-gray-600 italic">Trống</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Body --}}
            <div class="px-5 py-4">

                {{-- GPS info --}}
                <div class="mb-3 flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                    <span x-show="geoLoading" class="inline-flex items-center gap-1.5">
                        <svg class="h-3 w-3 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Đang lấy vị trí GPS...
                    </span>
                    <span x-show="!geoLoading && latitude" class="inline-flex items-center gap-1.5 text-success-600 dark:text-success-400">
                        <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        GPS sẵn sàng
                    </span>
                    <span x-show="!geoLoading && !latitude && geoError" class="inline-flex items-center gap-1.5">
                        <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18A9 9 0 0112 3z"/>
                        </svg>
                        Không có GPS – dùng xác thực IP
                    </span>
                </div>

                {{-- Action buttons --}}
                <div class="space-y-2.5">
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            wire:click="checkIn"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60 cursor-wait"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-success-600 px-4 py-2.5 text-sm font-semibold text-white transition duration-150 hover:bg-success-700 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-2 dark:bg-success-600 dark:hover:bg-success-500"
                        >
                            <svg wire:loading.remove wire:target="checkIn" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            <svg wire:loading wire:target="checkIn" class="h-4 w-4 shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span>{{ trans('packages.employee::employee_attendance.actions.check_in') }}</span>
                        </button>

                        <button
                            @if($hasOpenAttendance && $checkoutMinimumMinutes > 0 && $checkInDiffMinutes < $checkoutMinimumMinutes)
                                wire:click="mountAction('checkOutAction')"
                            @else
                                wire:click="checkOut"
                            @endif
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60 cursor-wait"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-danger-600 px-4 py-2.5 text-sm font-semibold text-white transition duration-150 hover:bg-danger-700 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 dark:bg-danger-600 dark:hover:bg-danger-500"
                        >
                            <svg wire:loading.remove wire:target="checkOut" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <svg wire:loading wire:target="checkOut" class="h-4 w-4 shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span>{{ trans('packages.employee::employee_attendance.actions.check_out') }}</span>
                        </button>
                    </div>

                    @if ($todayRecord && $todayRecord->flagged_location)
                        <div class="flex items-start gap-2 rounded-xl border border-warning-200 bg-warning-50 px-3.5 py-2.5 text-xs text-warning-700 dark:border-warning-700/50 dark:bg-warning-900/20 dark:text-warning-400">
                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18A9 9 0 0112 3z"/>
                            </svg>
                            <span>Chấm công ngoài vị trí – chờ Admin duyệt</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</div>
