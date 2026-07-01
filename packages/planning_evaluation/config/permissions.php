<?php

return [
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
            'approve' => 'Duyệt kế hoạch',
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
            'approve' => 'Duyệt đánh giá',
        ],
    ],
];
