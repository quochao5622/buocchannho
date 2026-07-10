<?php

return [
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
            'approve' => 'Duyệt kiểm kho',
        ],
    ],
];
