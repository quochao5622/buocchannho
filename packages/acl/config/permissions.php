<?php

return [

    'students' => [
        'label' => 'Học sinh',
        'icon' => 'heroicon-o-academic-cap',
        'permissions' => [
            'index' => 'Xem danh sách học sinh',
            'create' => 'Thêm mới học sinh',
            'edit' => 'Chỉnh sửa học sinh',
            'show' => 'Xem chi tiết học sinh',
            'destroy' => 'Xóa học sinh',
            'assign' => 'Gán giáo viên phụ trách cho học sinh',
            'view_all' => 'Xem toàn bộ học sinh (không giới hạn phân công)',
        ],
    ],
    'employees' => [
        'label' => 'Giáo viên',
        'icon' => 'heroicon-o-user-group',
        'permissions' => [
            'index' => 'Xem danh sách giáo viên',
            'create' => 'Thêm mới giáo viên',
            'edit' => 'Chỉnh sửa giáo viên',
            'show' => 'Xem chi tiết giáo viên',
            'destroy' => 'Xóa giáo viên',
        ],
    ],
    'plannings' => [
        'label' => 'Kế hoạch học tập',
        'icon' => 'heroicon-o-document-text',
        'permissions' => [
            'index' => 'Xem danh sách kế hoạch',
            'create' => 'Thêm mới kế hoạch',
            'edit' => 'Chỉnh sửa kế hoạch',
            'show' => 'Xem chi tiết kế hoạch',
            'destroy' => 'Xóa kế hoạch',
            'export' => 'Xuất Word kế hoạch',
            'tracker' => 'Xem báo cáo tổng quan ai đã nộp kế hoạch (Tracker)',
            'progress' => 'Xem báo cáo tiến độ học sinh',
            'view_all' => 'Xem toàn bộ kế hoạch (không giới hạn phân công)',
        ],
    ],
    'evaluations' => [
        'label' => 'Đánh giá học tập',
        'icon' => 'heroicon-o-clipboard-document-check',
        'permissions' => [
            'index' => 'Xem danh sách đánh giá',
            'create' => 'Thêm mới đánh giá',
            'edit' => 'Chỉnh sửa đánh giá',
            'show' => 'Xem chi tiết đánh giá',
            'destroy' => 'Xóa đánh giá',
            'export' => 'Xuất Word đánh giá',
            'view_all' => 'Xem toàn bộ đánh giá (không giới hạn phân công)',
        ],
    ],
    'equipments' => [
        'label' => 'Học cụ',
        'icon' => 'heroicon-o-briefcase',
        'permissions' => [
            'index' => 'Xem danh sách học cụ',
            'create' => 'Thêm mới học cụ',
            'edit' => 'Chỉnh sửa học cụ',
            'show' => 'Xem chi tiết học cụ',
            'destroy' => 'Xóa học cụ',
        ],
    ],
    'equipment_categories' => [
        'label' => 'Danh mục học cụ',
        'icon' => 'heroicon-o-tag',
        'permissions' => [
            'index' => 'Xem danh sách danh mục học cụ',
            'create' => 'Thêm mới danh mục học cụ',
            'edit' => 'Chỉnh sửa danh mục học cụ',
            'show' => 'Xem chi tiết danh mục học cụ',
            'destroy' => 'Xóa danh mục học cụ',
        ],
    ],
    'equipment_inventories' => [
        'label' => 'Kiểm kho học cụ',
        'icon' => 'heroicon-o-archive-box',
        'permissions' => [
            'index' => 'Xem danh sách kiểm kho',
            'create' => 'Thêm mới kiểm kho',
            'edit' => 'Chỉnh sửa kiểm kho',
            'show' => 'Xem chi tiết kiểm kho',
            'destroy' => 'Xóa kiểm kho',
        ],
    ],
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
];
