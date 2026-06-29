<?php

return [
    'resource' => [
        'model_label' => 'Lịch sử hoạt động',
        'plural_model_label' => 'Lịch sử hoạt động',
        'navigation_group' => 'Hệ thống',
    ],
    'fields' => [
        'id' => 'ID',
        'log_name' => 'Phân loại',
        'description' => 'Mô tả',
        'subject_type' => 'Loại đối tượng',
        'subject_id' => 'ID đối tượng',
        'causer_type' => 'Loại người dùng',
        'causer_id' => 'ID người dùng',
        'properties' => 'Thuộc tính',
        'activity' => 'Hoạt động',
        'causer_name' => 'Thực hiện bởi',
        'created_at' => 'Thời gian',
    ],
    'events' => [
        'login' => 'Đăng nhập vào hệ thống',
        'logout' => 'Đăng xuất khỏi hệ thống',
    ],
];
