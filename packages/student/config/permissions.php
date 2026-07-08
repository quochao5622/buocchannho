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
    'student_leave_requests' => [
        'label' => 'Nghỉ phép học sinh',
        'icon' => 'heroicon-o-calendar',
        'permissions' => [
            'index' => 'Xem danh sách nghỉ phép học sinh',
            'create' => 'Tạo đơn xin nghỉ phép học sinh',
            'edit' => 'Chỉnh sửa đơn nghỉ phép học sinh',
            'destroy' => 'Xóa đơn nghỉ phép học sinh',
            'view_all' => 'Xem đơn nghỉ phép toàn bộ học sinh',
        ],
    ],
];
