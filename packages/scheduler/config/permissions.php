<?php

return [
    'schedules' => [
        'label' => 'Quản lý lịch học',
        'icon' => 'heroicon-o-calendar',
        'permissions' => [
            'index' => 'Xem danh sách lịch học',
            'create' => 'Thêm mới lịch học',
            'edit' => 'Chỉnh sửa lịch học',
            'show' => 'Xem chi tiết lịch học',
            'destroy' => 'Xóa lịch học',
        ],
    ],
    'attendances' => [
        'label' => 'Điểm danh & Chấm công đứng lớp',
        'icon' => 'heroicon-o-check-circle',
        'permissions' => [
            'index' => 'Xem danh sách điểm danh',
            'create' => 'Thêm mới điểm danh',
            'edit' => 'Chỉnh sửa điểm danh',
            'show' => 'Xem chi tiết điểm danh',
            'destroy' => 'Xóa điểm danh',
        ],
    ],
];
