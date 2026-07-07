<?php

return [
    'navigation_label' => 'Nghỉ phép',
    'model_label' => 'Đơn xin nghỉ học',
    'plural_model_label' => 'Đơn xin nghỉ học',
    'navigation_group' => 'Quản lý học sinh',
    'fields' => [
        'student_id' => 'Học sinh',
        'start_date' => 'Từ ngày',
        'end_date' => 'Đến ngày',
        'reason' => 'Lý do nghỉ',
        'status' => 'Trạng thái',
        'approved_by' => 'Người duyệt',
        'approved_at' => 'Thời điểm duyệt',
        'rejection_reason' => 'Lý do từ chối',
        'created_by' => 'Người tạo',
    ],
    'status' => [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ],
    'actions' => [
        'approve' => 'Duyệt đơn nghỉ học',
        'approve_confirm' => 'Bạn có chắc chắn muốn duyệt đơn xin nghỉ học này?',
        'approve_success' => 'Đã duyệt đơn xin nghỉ học thành công.',
        'reject' => 'Từ chối đơn nghỉ học',
        'reject_confirm' => 'Bạn có chắc chắn muốn từ chối đơn xin nghỉ học này?',
        'reject_success' => 'Đã từ chối đơn xin nghỉ học.',
    ],
];
