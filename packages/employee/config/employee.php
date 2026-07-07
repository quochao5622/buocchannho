<?php

return [
    'check_in' => [
        // Tọa độ GPS mặc định của trung tâm (kinh độ/vĩ độ)
        // Vui lòng cập nhật tọa độ chính xác của trung tâm tại đây
        'latitude' => 10.762622,
        'longitude' => 106.660172,

        // Bán kính sai số cho phép khi chấm công (mét)
        'allowed_radius_meters' => 100,

        // Quy định giờ giấc làm việc ca hành chính
        'work_start_time' => '08:00', // Giờ bắt đầu
        'grace_period_minutes' => 15,  // Thời gian ân hạn cho phép đi muộn (phút)

        // Thời điểm kết thúc ca hành chính.
        // Bất kỳ lượt chấm công nào từ thời điểm này trở đi được coi là ca tối/cá nhân, không xét đi trễ.
        'office_hours_end_threshold' => '13:00',
    ],
];
