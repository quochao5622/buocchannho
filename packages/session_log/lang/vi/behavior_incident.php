<?php

return [
    'navigation_label' => 'Nhật ký hành vi (ABC)',
    'model_label' => 'Nhật ký hành vi',
    'plural_model_label' => 'Nhật ký hành vi',
    'navigation_group' => 'Nhật ký & Trị liệu',

    'fields' => [
        'student_id' => 'Học sinh',
        'incident_date' => 'Thời gian xảy ra',
        'intensity' => 'Mức độ nghiêm trọng',
        'duration_minutes' => 'Thời lượng diễn ra',
        'antecedent' => 'Hoàn cảnh (A)',
        'behavior' => 'Hành vi (B)',
        'consequence' => 'Hệ quả (C)',
        'notes' => 'Ghi chú thêm',
        'employee' => 'Giáo viên ghi',
    ],

    'sections' => [
        'antecedent' => [
            'heading' => 'A - Antecedent',
            'description' => 'Hoàn cảnh trước khi xảy ra hành vi',
            'placeholder' => 'Ví dụ: Khi giáo viên yêu cầu trẻ cất đồ chơi để chuẩn bị vào bàn học...',
        ],
        'behavior' => [
            'heading' => 'B - Behavior',
            'description' => 'Hành vi cụ thể của trẻ',
            'placeholder' => 'Ví dụ: Trẻ la hét, ném đồ chơi và nằm khóc ăn vạ trên sàn...',
        ],
        'consequence' => [
            'heading' => 'C - Consequence',
            'description' => 'Hệ quả / Phản ứng của giáo viên',
            'placeholder' => 'Ví dụ: Giáo viên giữ thái độ bình tĩnh, chờ trẻ dịu lại rồi cùng trẻ cất đồ chơi...',
        ],
    ],

    'intensity' => [
        'mild' => '🟢 Nhẹ',
        'moderate' => '🟡 Trung bình',
        'high' => '🟠 Cao',
        'severe' => '🔴 Nghiêm trọng',
    ],

    'units' => [
        'minutes' => 'phút',
    ],

    'helpers' => [
        'character_count' => ':current / :max ký tự',
    ],

    'actions' => [
        'delete' => [
            'heading' => 'Xóa nhật ký hành vi',
            'description' => 'Bạn có chắc chắn muốn xóa nhật ký hành vi này không?',
            'submit' => 'Xóa',
            'cancel' => 'Hủy',
        ],
        'bulk_delete' => [
            'heading' => 'Xóa các nhật ký hành vi đã chọn',
            'description' => 'Bạn có chắc chắn muốn xóa các nhật ký hành vi này không?',
        ],
    ],
];
