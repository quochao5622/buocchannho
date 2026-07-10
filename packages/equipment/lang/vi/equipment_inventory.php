<?php

return [
    'navigation_group' => 'Học cụ',
    'navigation_label' => 'Kiểm kê học cụ',
    'model_label' => 'Phiếu kiểm kê',
    'plural_model_label' => 'Phiếu kiểm kê',

    'fields' => [
        'inventory_code' => 'Mã phiếu',
        'inventory_date' => 'Ngày kiểm kê',
        'status' => 'Trạng thái',
        'condition_status' => 'Tình trạng',
        'notes' => 'Ghi chú',
        'detail_equipment_search' => 'Tìm học cụ',
        'detail_equipment_search_placeholder' => 'Nhập mã, tên hoặc vị trí để lọc nhanh trong danh sách bên dưới',
        'detail_equipment_search_helper' => 'Gõ để lọc trực tiếp các mục trong repeater và tô sáng phần khớp.',
        'details' => 'Chi tiết kiểm kê',
        'equipment_image' => 'Hình',
        'equipment' => 'Học cụ',
        'quantity_expected' => 'SL dự kiến',
        'quantity_actual' => 'SL thực tế',
        'inspector' => 'Người kiểm kê',
        'updated_at' => 'Cập nhật',
    ],

    'search' => [
        'placeholder' => 'Nhập mã, tên hoặc vị trí để lọc nhanh trong danh sách bên dưới',
        'helper' => 'Gõ để lọc trực tiếp các mục trong repeater và tô sáng phần khớp.',
    ],

    'details' => [
        'helper' => 'Tìm kiếm lọc trực tiếp danh sách bên dưới. Khi lưu vẫn giữ đầy đủ dữ liệu kiểm kê.',
        'empty_label' => 'Học cụ',
    ],

    'table' => [
        'inventory_code' => 'Mã phiếu',
        'inventory_date' => 'Ngày',
        'inspector' => 'Người kiểm kê',
        'status' => 'Trạng thái',
        'updated_at' => 'Cập nhật',
    ],

    'filters' => [
        'status' => 'Trạng thái',
    ],

    'approve' => [
        'label' => 'Cập nhật kho',
        'success' => 'Đã duyệt phiếu và cập nhật tồn kho.',
        'error' => 'Lỗi khi duyệt phiếu.',
    ],
    'status' => [
        'draft' => 'Bản nháp',
        'completed' => 'Đã kiểm kê',
        'approved' => 'Đã duyệt',
    ],
];
