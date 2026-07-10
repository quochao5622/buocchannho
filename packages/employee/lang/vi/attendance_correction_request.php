<?php

return [
    'navigation_label' => 'Yêu cầu điều chỉnh',
    'model_label' => 'Yêu cầu điều chỉnh chấm công',
    'plural_model_label' => 'Yêu cầu điều chỉnh chấm công',
    'navigation_group' => 'Quản lý giáo viên',
    'fields' => [
        'employee_id' => 'Giáo viên',
        'attendance_date' => 'Ngày cần điều chỉnh',
        'requested_check_in_at' => 'Giờ check-in thực tế',
        'requested_check_out_at' => 'Giờ check-out thực tế',
        'reason' => 'Lý do',
        'status' => 'Trạng thái',
        'reviewed_by' => 'Người xét duyệt',
        'reviewed_at' => 'Thời điểm xét duyệt',
        'review_note' => 'Ghi chú xét duyệt',
    ],
    'status' => [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Bị từ chối',
    ],
    'actions' => [
        'approve' => 'Duyệt',
        'reject' => 'Từ chối',
        'approve_confirm' => 'Xác nhận duyệt yêu cầu này?',
        'reject_confirm' => 'Xác nhận từ chối yêu cầu này?',
        'approve_success' => 'Đã duyệt yêu cầu điều chỉnh chấm công.',
        'reject_success' => 'Đã từ chối yêu cầu điều chỉnh chấm công.',
        'review_note_label' => 'Lý do từ chối (tùy chọn)',
        'already_reviewed' => 'Yêu cầu này đã được xét duyệt.',
    ],
];
