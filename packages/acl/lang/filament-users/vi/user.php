<?php

return [
    'group' => 'Hệ thống',
    'resource' => [
        'id' => 'ID',
        'single' => 'Người dùng',
        'email_verified_at' => 'Xác minh Email',
        'created_at' => 'Ngày tạo',
        'updated_at' => 'Ngày cập nhật',
        'verified' => 'Đã xác minh',
        'unverified' => 'Chưa xác minh',
        'name' => 'Tên',
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'password_confirmation' => 'Xác nhận mật khẩu',
        'roles' => 'Vai trò',
        'teams' => 'Nhóm',
        'label' => 'Người dùng',
        'title' => [
            'show' => 'Xem người dùng',
            'delete' => 'Xóa người dùng',
            'impersonate' => 'Đóng vai người dùng',
            'create' => 'Tạo người dùng',
            'edit' => 'Sửa người dùng',
            'list' => 'Danh sách người dùng',
            'home' => 'Người dùng',
        ],
        'notificaitons' => [
            'last' => [
                'title' => 'Lỗi',
                'body' => 'Bạn không thể xóa người dùng cuối cùng',
            ],
            'self' => [
                'title' => 'Lỗi',
                'body' => 'Bạn không thể tự xóa chính mình',
            ],
        ],
        'avatar' => 'Ảnh đại diện',
        'change_password' => 'Đổi mật khẩu',
        'change_password_auto' => 'Mật khẩu đã được đổi tự động',
        'change_password_success' => 'Đổi mật khẩu thành công',
        'change_password_auto_body' => 'Mật khẩu đã được đổi tự động',
        'change_password_success_body' => 'Đổi mật khẩu thành công',
        'change_password_auto_body_placeholder' => 'Để trống để tự động tạo',
        'change_password_success_body_placeholder' => 'Để trống để tự động tạo',
    ],
    'bulk' => [
        'teams' => 'Cập nhật Nhóm',
        'roles' => 'Cập nhật Vai trò',
    ],
    'team' => [
        'title' => 'Nhóm',
        'single' => 'Nhóm',
        'columns' => [
            'avatar' => 'Ảnh đại diện',
            'name' => 'Tên',
            'owner' => 'Chủ sở hữu',
            'personal_team' => 'Nhóm cá nhân',
        ],
    ],
    'banner' => [
        'impersonating' => 'Đang đóng vai',
        'leave' => 'Thoát đóng vai',
    ],
];
