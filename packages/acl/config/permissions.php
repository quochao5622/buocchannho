<?php

return [

    'users' => [
        'label' => 'Người dùng',
        'icon' => 'heroicon-o-users',
        'permissions' => [
            'index' => 'Xem danh sách người dùng',
            'create' => 'Thêm mới người dùng',
            'edit' => 'Chỉnh sửa người dùng',
            'show' => 'Xem chi tiết người dùng',
            'destroy' => 'Xóa người dùng',
        ],
    ],
    'roles' => [
        'label' => 'Vai trò & Phân quyền',
        'icon' => 'heroicon-o-shield-check',
        'permissions' => [
            'index' => 'Xem danh sách vai trò',
            'create' => 'Thêm mới vai trò',
            'edit' => 'Chỉnh sửa vai trò',
            'show' => 'Xem chi tiết vai trò',
            'destroy' => 'Xóa vai trò',
        ],
    ],
    'logs' => [
        'label' => 'Nhật ký hệ thống',
        'icon' => 'heroicon-o-document-magnifying-glass',
        'permissions' => [
            'index' => 'Xem nhật ký hệ thống',
        ],
    ],
    'audits' => [
        'label' => 'Nhật ký kiểm toán',
        'icon' => 'heroicon-o-clock',
        'permissions' => [
            'index' => 'Xem nhật ký kiểm toán dữ liệu',
            'restore' => 'Khôi phục lịch sử dữ liệu',
        ],
    ],
    'activities' => [
        'label' => 'Nhật ký hoạt động',
        'icon' => 'heroicon-o-clipboard-document-list',
        'permissions' => [
            'index' => 'Xem danh sách nhật ký hoạt động',
            'view' => 'Xem chi tiết nhật ký hoạt động',
        ],
    ],

];
