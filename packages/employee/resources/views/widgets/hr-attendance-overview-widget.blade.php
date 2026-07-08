<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center space-x-2">
                <x-filament::icon
                    icon="heroicon-m-users"
                    class="h-6 w-6 text-primary-500"
                />
                <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Tổng quan nhân sự & Chấm công hôm nay</span>
            </div>
        </x-slot>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
            <!-- Thống kê đi làm -->
            <div style="display: flex; flex-direction: column; height: 100%;" class="p-6 bg-white border border-gray-100 rounded-2xl shadow-sm dark:bg-gray-900 dark:border-gray-800 transition-all hover:scale-[1.02] duration-300">
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tỉ lệ chuyên cần</span>
                    <div class="flex items-baseline space-x-2 mt-2">
                        <span class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $checkInRate }}%</span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">({{ $todayCheckedIn }}/{{ $totalEmployees }} nhân viên)</span>
                    </div>
                    <!-- Progress bar với style inline để đảm bảo hiển thị -->
                    <div style="width: 100%; background-color: #f3f4f6; border-radius: 9999px; height: 0.5rem; margin-top: 0.75rem; overflow: hidden; display: flex;" class="dark:bg-gray-700">
                        <div style="background-color: rgb(var(--primary-600, 2, 132, 199)); height: 0.5rem; border-radius: 9999px; transition: width 0.5s; width: {{ $checkInRate }}%;"></div>
                    </div>
                </div>
                <div style="margin-top: auto; padding-top: 1.5rem;" class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400 border-t border-gray-50 dark:border-gray-800">
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-yellow-500 mr-1.5"></span>Muộn: {{ $todayLate }}</span>
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-1.5"></span>Vắng: {{ $todayAbsent }}</span>
                    <a href="{{ $attendanceUrl }}" class="text-primary-600 hover:underline font-medium">Chi tiết &rarr;</a>
                </div>
            </div>

            <!-- Phê duyệt đơn nghỉ phép -->
            <div style="display: flex; flex-direction: column; height: 100%;" class="p-6 bg-white border border-gray-100 rounded-2xl shadow-sm dark:bg-gray-900 dark:border-gray-800 transition-all hover:scale-[1.02] duration-300">
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Đơn xin nghỉ phép</span>
                    <div class="flex items-baseline space-x-2 mt-2">
                        <span class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $pendingLeaves }}</span>
                        <span class="text-xs text-yellow-600 dark:text-yellow-500 bg-yellow-50 dark:bg-yellow-950/30 px-2 py-0.5 rounded-full font-medium">Đang chờ duyệt</span>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 leading-relaxed">
                        Xem và phê duyệt các yêu cầu xin nghỉ phép hoặc nghỉ ốm của giáo viên.
                    </p>
                </div>
                <div style="margin-top: auto; padding-top: 1.5rem;">
                    <x-filament::button href="{{ $leaveUrl }}" tag="a" color="primary" style="width: 100%; border-radius: 0.75rem;">
                        Xử lý đơn nghỉ &rarr;
                    </x-filament::button>
                </div>
            </div>

            <!-- Yêu cầu sửa chấm công -->
            <div style="display: flex; flex-direction: column; height: 100%;" class="p-6 bg-white border border-gray-100 rounded-2xl shadow-sm dark:bg-gray-900 dark:border-gray-800 transition-all hover:scale-[1.02] duration-300">
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Yêu cầu sửa công</span>
                    <div class="flex items-baseline space-x-2 mt-2">
                        <span class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $pendingCorrections }}</span>
                        <span class="text-xs text-yellow-600 dark:text-yellow-500 bg-yellow-50 dark:bg-yellow-950/30 px-2 py-0.5 rounded-full font-medium">Đang chờ duyệt</span>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 leading-relaxed">
                        Giáo viên gửi các yêu cầu sửa lỗi check-in/check-out quên bấm hoặc sai lệch.
                    </p>
                </div>
                <div style="margin-top: auto; padding-top: 1.5rem;">
                    <x-filament::button href="{{ $correctionUrl }}" tag="a" color="primary" style="width: 100%; border-radius: 0.75rem;">
                        Xử lý yêu cầu &rarr;
                    </x-filament::button>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
