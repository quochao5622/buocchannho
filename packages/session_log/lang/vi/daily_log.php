<?php

return [
    'navigation_label' => 'Nhật ký ngày',
    'model_label' => 'Nhật ký ngày',
    'plural_model_label' => 'Nhật ký ngày',
    'navigation_group' => 'Nhật ký & Trị liệu',

    'fields' => [
        'student_id' => 'Học sinh',
        'log_date' => 'Ngày ghi nhận',
        'emotion' => 'Cảm xúc',
        'focus_level' => 'Mức độ tập trung',
        'cooperation_level' => 'Khả năng hợp tác',
        'eating_note' => 'Ghi chú ăn uống',
        'sleeping_note' => 'Ghi chú giấc ngủ',
        'hygiene_note' => 'Ghi chú vệ sinh',
        'general_note' => 'Tiến bộ nổi bật / Ghi chú chung',
        'status' => 'Trạng thái',
        'send_notification' => 'Gửi thông báo tới phụ huynh (Tính năng đang phát triển)',
        'employee' => 'Giáo viên ghi',
        'focus_level_short' => 'Tập trung',
        'cooperation_level_short' => 'Hợp tác',
    ],

    'emotion' => [
        'happy' => 'Vui vẻ',
        'normal' => 'Bình thường',
        'irritable' => 'Cáu gắt',
        'hyperactive' => 'Tăng động',
    ],

    'rating' => [
        'good' => 'Tốt',
        'normal' => 'Trung bình',
        'poor' => 'Kém',
    ],

    'status' => [
        'draft' => 'Bản nháp',
        'completed' => 'Hoàn thành',
    ],

    'helpers' => [
        'character_count' => ':current / :max ký tự',
    ],

    'actions' => [
        'delete' => [
            'heading' => 'Xóa nhật ký ngày',
            'description' => 'Bạn có chắc chắn muốn xóa nhật ký ngày này không?',
            'submit' => 'Xóa',
            'cancel' => 'Hủy',
        ],
        'bulk_delete' => [
            'heading' => 'Xóa các nhật ký ngày đã chọn',
            'description' => 'Bạn có chắc chắn muốn xóa các nhật ký ngày này không?',
        ],
    ],
];
