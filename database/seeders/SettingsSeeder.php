<?php

namespace Database\Seeders;

use DamodarBhattarai\Settings\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $generalSettings = [
            'site_name' => ['value' => 'Site Name', 'label' => 'Site Name', 'type' => 'text'],
            'currency' => ['value' => '$', 'label' => 'Currency', 'type' => 'text'],
            'header_logo' => ['value' => '', 'label' => 'Header Logo', 'type' => 'image'],
            'footer_logo' => ['value' => '', 'label' => 'Footer Logo', 'type' => 'image'],
            'site_favicon' => ['value' => '', 'label' => 'Favicon', 'type' => 'image'],
            'primary_email' => ['value' => 'damodar.bhattarai.1999@gmail.com', 'label' => 'Primary Email', 'type' => 'text'],
            'secondary_email' => ['value' => '', 'label' => 'Secondary Email', 'type' => 'text'],
            'primary_phone' => ['value' => '9825710275', 'label' => 'Primary Phone', 'type' => 'text'],
            'secondary_phone' => ['value' => '', 'label' => 'Secondary Phone', 'type' => 'text'],
            'address' => ['value' => 'Koteshwor, Kathmandu', 'label' => 'Address', 'type' => 'textarea'],
            'google_map' => ['value' => '', 'label' => 'Google Map Embed URL', 'type' => 'text'],
            'facebook_url' => ['value' => '', 'label' => 'Facebook URL', 'type' => 'text'],
            'twitter_url' => ['value' => '', 'label' => 'Twitter / X URL', 'type' => 'text'],
            'instagram_url' => ['value' => '', 'label' => 'Instagram URL', 'type' => 'text'],
            'youtube_url' => ['value' => '', 'label' => 'YouTube URL', 'type' => 'text'],
            'google_analytics' => ['value' => '', 'label' => 'Google Analytics ID', 'type' => 'text'],
            'google_tag_manager' => ['value' => '', 'label' => 'Google Tag Manager ID', 'type' => 'text'],
        ];

        foreach ($generalSettings as $key => $data) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => json_encode($data['value']),
                    'label' => $data['label'],
                    'type' => $data['type'],
                    'group' => 'general',
                    'tab_order' => 0,
                ]
            );
        }

        $attendanceSettings = [
            'office_morning_start' => [
                'value' => '08:00',
                'label' => 'Giờ bắt đầu ca sáng',
                'type' => 'time',
                'tab_order' => 1,
            ],
            'office_morning_end' => [
                'value' => '12:00',
                'label' => 'Giờ kết thúc ca sáng',
                'type' => 'time',
                'tab_order' => 1,
            ],
            'office_afternoon_start' => [
                'value' => '13:30',
                'label' => 'Giờ bắt đầu ca chiều',
                'type' => 'time',
                'tab_order' => 1,
            ],
            'office_afternoon_end' => [
                'value' => '17:30',
                'label' => 'Giờ kết thúc ca chiều',
                'type' => 'time',
                'tab_order' => 1,
            ],
            'office_evening_start' => [
                'value' => '18:00',
                'label' => 'Giờ bắt đầu ca tối',
                'type' => 'time',
                'tab_order' => 1,
            ],
            'office_evening_end' => [
                'value' => '21:00',
                'label' => 'Giờ kết thúc ca tối',
                'type' => 'time',
                'tab_order' => 1,
            ],
            'office_allowed_late_minutes' => [
                'value' => 15,
                'label' => 'Thời gian đi muộn cho phép (phút)',
                'type' => 'text',
                'tab_order' => 2,
            ],
            'center_latitude' => [
                'value' => '10.79690000',
                'label' => 'Vĩ độ của trung tâm (Latitude)',
                'type' => 'text',
                'tab_order' => 2,
            ],
            'center_longitude' => [
                'value' => '106.71280000',
                'label' => 'Kinh độ của trung tâm (Longitude)',
                'type' => 'text',
                'tab_order' => 2,
            ],
            'allowed_radius_meters' => [
                'value' => 500,
                'label' => 'Bán kính chấm công cho phép (mét)',
                'type' => 'text',
                'tab_order' => 2,
            ],
            'office_wifi_ips' => [
                'value' => '',
                'label' => 'IP WiFi văn phòng (phân cách bằng dấu phẩy)',
                'type' => 'text',
                'tab_order' => 2,
            ],
            'checkout_minimum_minutes' => [
                'value' => 5,
                'label' => 'Thời gian t.thiểu check-in -> check-out để bỏ qua cảnh báo (phút)',
                'type' => 'text',
                'tab_order' => 2,
            ],
            'auto_checkout_enabled' => [
                'value' => true,
                'label' => 'Kích hoạt tự động Check-out',
                'type' => 'switch',
                'tab_order' => 2,
            ],
            'auto_checkout_time' => [
                'value' => '17:30',
                'label' => 'Giờ tự động Check-out',
                'type' => 'time',
                'tab_order' => 2,
            ],
            'open_session_notify_admin' => [
                'value' => true,
                'label' => 'Thông báo Admin khi tự động Check-out',
                'type' => 'switch',
                'tab_order' => 2,
            ],
            'allow_flagged_location' => [
                'value' => false,
                'label' => 'Cho phép chấm công ngoài khu vực (Cần duyệt)',
                'type' => 'switch',
                'tab_order' => 2,
            ],
            'schedule_early_checkin_minutes' => [
                'value' => 30,
                'label' => 'Cho phép check-in trước giờ học tối đa (phút)',
                'type' => 'text',
                'tab_order' => 2,
            ],
            'schedule_late_checkin_minutes' => [
                'value' => 30,
                'label' => 'Cho phép check-in muộn sau giờ học tối đa (phút)',
                'type' => 'text',
                'tab_order' => 2,
            ],
        ];

        foreach ($attendanceSettings as $key => $data) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => json_encode($data['value']),
                    'label' => $data['label'],
                    'type' => $data['type'],
                    'group' => 'attendance',
                    'tab_order' => $data['tab_order'] ?? 1,
                ]
            );
        }

        cache()->forget('damodarbhattarai-settings');
    }
}
