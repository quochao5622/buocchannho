<?php

return [
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
    'employee_attendances' => [
        'label' => 'Chấm công giáo viên',
        'icon' => 'heroicon-o-clock',
        'permissions' => [
            'index' => 'Xem danh sách chấm công',
            'create' => 'Thêm mới chấm công thủ công',
            'edit' => 'Chỉnh sửa chấm công',
            'show' => 'Xem chi tiết chấm công',
            'destroy' => 'Xóa chấm công',
            'view_all' => 'Xem chấm công toàn bộ giáo viên',
            'approve_flagged_location' => 'Duyệt chấm công ngoài vị trí',
            'reject_flagged_location' => 'Từ chối chấm công ngoài vị trí',
            'manage' => 'Quản lý chấm công (chấm bù, duyệt)',
            'view_overview' => 'Xem widget tổng quan nhân sự & chấm công',
        ],
    ],
    'leave_requests' => [
        'label' => 'Quản lý nghỉ phép',
        'icon' => 'heroicon-o-calendar-days',
        'permissions' => [
            'index' => 'Xem danh sách đơn nghỉ phép',
            'create' => 'Tạo đơn xin nghỉ phép',
            'edit' => 'Chỉnh sửa đơn nghỉ phép',
            'destroy' => 'Xóa đơn nghỉ phép',
            'approve' => 'Duyệt/Từ chối đơn nghỉ phép',
            'view_all' => 'Xem đơn nghỉ phép toàn bộ giáo viên',
        ],
    ],
    'attendance_correction_requests' => [
        'label' => 'Yêu cầu điều chỉnh chấm công',
        'icon' => 'heroicon-o-pencil-square',
        'permissions' => [
            'index' => 'Xem danh sách yêu cầu điều chỉnh',
            'create' => 'Gửi yêu cầu điều chỉnh chấm công',
            'edit' => 'Chỉnh sửa yêu cầu điều chỉnh',
            'show' => 'Xem chi tiết yêu cầu điều chỉnh',
            'destroy' => 'Xóa yêu cầu điều chỉnh',
            'approve' => 'Duyệt yêu cầu điều chỉnh',
            'reject' => 'Từ chối yêu cầu điều chỉnh',
            'view_all' => 'Xem tất cả yêu cầu điều chỉnh',
        ],
    ],
];
