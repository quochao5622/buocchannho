<?php

declare(strict_types=1);

return [
    'placeholder' => 'N/A',
    'navigation' => [
        'title' => 'Xem Log',
        'heading' => 'Bảng Log',
        'subheading' => '',
        'group' => 'Hệ thống',
        'label' => 'Xem Log',
    ],
    'table' => [
        'model_label' => 'nhật ký',
        'plural_model_label' => 'nhật ký',
        'columns' => [
            'log_level' => 'Mức độ',
            'env' => 'Môi trường',
            'file' => 'Tên file',
            'message' => 'Nội dung (Tóm tắt)',
            'date' => 'Thời gian',
        ],
        'filters' => [
            'env' => [
                'label' => 'Môi trường',
                'indicator' => 'Lọc theo môi trường',
            ],
            'file' => [
                'label' => 'File',
                'indicator' => 'Lọc theo file',
            ],
            'date' => [
                'label' => 'Ngày',
                'indicator' => 'Lọc theo ngày',
                'from' => 'Từ',
                'until' => 'Đến',
            ],
            'date_range' => [
                'label' => 'Khoảng thời gian',
                'indicator' => 'Lọc theo khoảng thời gian',
            ],
            'indicators' => [
                'logs_from_to' => 'Log từ :from đến :until',
                'logs_from' => 'Log từ :from',
                'logs_until' => 'Log đến :until',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Xem chi tiết',
                'heading' => 'Chi tiết lỗi',
            ],
            'read' => [
                'label' => 'Đọc Email',
                'subject' => 'Tiêu đề',
                'mail_log' => 'Log Email',
                'sent_date' => 'Ngày gửi',
            ],
            'refresh' => [
                'label' => 'Làm mới',
            ],
            'clear' => [
                'label' => 'Xóa toàn bộ Log',
                'success' => 'Tất cả log đã được xóa thành công!',
            ],
            'copy_markdown' => [
                'label' => 'Sao chép dạng Markdown',
                'success' => 'Đã sao chép Markdown vào clipboard',
                'headers' => [
                    'file' => 'File',
                    'message' => 'Nội dung',
                    'description' => 'Mô tả',
                    'context' => 'Ngữ cảnh',
                    'stack_trace' => 'Nguồn gốc lỗi (Stack Trace)',
                    'mail' => 'Chi tiết Email',
                ],
            ],
        ],
    ],
    'schema' => [
        'error-log' => [
            'stack' => 'Nguồn gốc lỗi (Stack Trace)',
        ],
        'json-log' => [
            'context' => 'Ngữ cảnh (Context)',
        ],
    ],
    'mail' => [
        'sender' => [
            'label' => 'Người gửi',
            'name' => 'Tên',
            'email' => 'Email',
        ],
        'receiver' => [
            'label' => 'Người nhận',
            'name' => 'Tên',
            'email' => 'Email',
        ],
        'content' => 'Nội dung',
        'plain' => 'Văn bản thuần',
        'html' => 'HTML',
    ],
    'levels' => [
        'all' => 'Tất cả',
        'alert' => 'Cảnh báo',
        'critical' => 'Nghiêm trọng',
        'debug' => 'Gỡ lỗi (Debug)',
        'emergency' => 'Khẩn cấp',
        'error' => 'Lỗi',
        'info' => 'Thông tin',
        'notice' => 'Lưu ý',
        'warning' => 'Cảnh báo (Warning)',
        'mail' => 'Email',
    ],
];
