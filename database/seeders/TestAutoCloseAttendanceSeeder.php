<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Quochao56\Employee\Models\Employee;
use Quochao56\Employee\Models\EmployeeAttendance;
use Quochao56\Employee\Services\AttendanceCheckinService;
use App\Models\User;

class TestAutoCloseAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo một employee giả nếu chưa có
        $employee = Employee::firstOrCreate(
            ['email' => 'test_auto_close@opsgreat.com'],
            [
                'name' => 'Nhân viên Test Auto Close',
                'phone' => '0123456789',
                'status' => 'active',
            ]
        );

        // Xóa các bản ghi cũ của user này
        EmployeeAttendance::where('employee_id', $employee->id)->delete();

        // Tạo một phiên dở dang của ngày hôm trước
        // Điều kiện để được đóng tự động: check_out_at = null, check_in_at < hôm nay và cách hiện tại > 20 tiếng.
        $yesterday = now()->subDays(1);
        $checkInTime = $yesterday->copy()->setTime(8, 0, 0); // 8:00 AM hôm qua
        
        $openSession = EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'date' => $yesterday->toDateString(),
            'check_in_at' => $checkInTime,
            'check_in_ip' => '127.0.0.1',
            'status' => 'present',
            'verification_status' => 'approved',
            'notes' => 'Tạo qua seeder để test auto close',
        ]);

        $this->command->info("Đã tạo phiên check-in dở dang lúc: " . $checkInTime->format('Y-m-d H:i:s'));

        // Chạy service để checkIn hôm nay, service sẽ tự động quét và đóng phiên hôm qua
        $service = app(AttendanceCheckinService::class);
        
        $this->command->info("Bắt đầu thực thi checkIn hôm nay...");
        
        $newSession = $service->checkIn($employee, [
            'latitude' => null,
            'longitude' => null,
            'ip' => '127.0.0.1',
            'notes' => 'Check-in hôm nay',
        ]);

        $this->command->info("Check-in hôm nay thành công lúc: " . $newSession->check_in_at->format('Y-m-d H:i:s'));

        // Kiểm tra lại phiên hôm qua xem đã được đóng chưa
        $openSession->refresh();
        if ($openSession->auto_closed && $openSession->check_out_at) {
            $this->command->info("Phiên hôm qua ĐÃ ĐƯỢC ĐÓNG tự động!");
            $this->command->info("Giờ checkout được set là: " . $openSession->check_out_at->format('Y-m-d H:i:s'));
            $this->command->info("Total hours: " . $openSession->total_hours);
        } else {
            $this->command->error("Phiên hôm qua CHƯA được đóng!");
        }
        
        $this->command->info("Lưu ý: Notification đã được gửi vào database cho những admin có quyền 'employee_attendances.manage' hoặc Super Admin.");
    }
}
