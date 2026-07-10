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
            'view_all' => 'Xem tất cả lịch học',
        ],
    ],

    'schedule_exceptions' => [
        'label' => 'Điều chỉnh lịch học',
        'icon' => 'heroicon-o-exclamation-triangle',
        'permissions' => [
            'index' => 'Xem danh sách điều chỉnh lịch học',
            'create' => 'Thêm mới điều chỉnh lịch học',
            'edit' => 'Chỉnh sửa điều chỉnh lịch học',
            'show' => 'Xem chi tiết điều chỉnh lịch học',
            'destroy' => 'Xóa điều chỉnh lịch học',
            'approve' => 'Duyệt/Từ chối điều chỉnh lịch học',
        ],
    ],

    'classrooms' => [
        'label' => 'Quản lý phòng học',
        'icon' => 'heroicon-o-home-modern',
        'permissions' => [
            'index' => 'Xem danh sách phòng học',
            'create' => 'Thêm mới phòng học',
            'edit' => 'Chỉnh sửa phòng học',
            'show' => 'Xem chi tiết phòng học',
            'destroy' => 'Xóa phòng học',
        ],
    ],

    'calendar_overviews' => [
        'label' => 'Lịch biểu',
        'icon' => 'heroicon-o-calendar-days',
        'permissions' => [
            'index' => 'Xem lịch biểu',
        ],
    ],

    'daily_operations' => [
        'label' => 'Vận hành theo ngày',
        'icon' => 'heroicon-o-clipboard-document-check',
        'permissions' => [
            'index' => 'Xem vận hành theo ngày',
        ],
    ],

];
