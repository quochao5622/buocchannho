<?php

return [
    'common' => [
        'navigation_group' => 'Học cụ',
        'navigation_label' => 'Học cụ',
        'model_label' => 'Học cụ',
        'plural_model_label' => 'Học cụ',
        'updated_at' => 'Cập nhật',
        'status' => 'Trạng thái',
        'notes' => 'Ghi chú',
        'image' => 'Hình',
    ],

    'form' => [
        'equipment_code' => 'Mã học cụ',
        'name' => 'Tên học cụ',
        'category' => 'Danh mục',
        'image' => 'Hình ảnh',
        'quantity' => 'Số lượng',
        'status' => 'Tình trạng',
        'location' => 'Vị trí',
        'unit' => 'Đơn vị tính',
        'note' => 'Ghi chú',
        'updated_at' => 'Cập nhật',
        'export' => 'Xuất Excel',
    ],

    'fields' => [
        'name' => 'Tên học cụ',
        'unit' => 'Đơn vị tính',
        'category_id' => 'Danh mục',
        'quantity' => 'Số lượng',
        'status' => 'Tình trạng',
        'actual_quantity' => 'Số lượng thực tế',
        'note' => 'Ghi chú',
    ],

    'resource' => [
        'navigation_group' => 'Học cụ',
        'navigation_label' => 'Học cụ',
        'model_label' => 'Học cụ',
        'plural_model_label' => 'Học cụ',

        'fields' => [
            'equipment_code' => 'Mã học cụ',
            'name' => 'Tên học cụ',
            'category_id' => 'Danh mục',
            'image' => 'Hình ảnh',
            'quantity' => 'Số lượng',
            'status' => 'Tình trạng',
            'location' => 'Vị trí',
            'unit' => 'Đơn vị tính',
            'note' => 'Ghi chú',
            'actual_quantity' => 'Số lượng thực tế',
        ],

        'table' => [
            'code' => 'Mã',
            'name' => 'Tên',
            'image' => 'Hình',
            'category' => 'Danh mục',
            'quantity' => 'Số lượng',
            'location' => 'Vị trí',
            'updated_at' => 'Cập nhật',
        ],

        'filters' => [
            'category' => 'Danh mục',
            'status' => 'Trạng thái',
        ],

        'actions' => [
            'export_excel' => 'Xuất Excel',
        ],

        'export' => [
            'filename_prefix' => 'hoc-cu-',
            'columns' => [
                'image' => 'Image',
                'name' => 'Tên học cụ',
                'unit' => 'Đơn vị tính',
                'category' => 'Danh mục',
                'quantity' => 'Số lượng',
                'status' => 'Tình trạng',
                'actual_quantity' => 'Số lượng thực tế',
                'note' => 'Ghi chú',
            ],
        ],
    ],

    'category' => [
        'navigation_group' => 'Học cụ',
        'navigation_label' => 'Danh mục học cụ',
        'model_label' => 'Danh mục',
        'plural_model_label' => 'Danh mục học cụ',

        'fields' => [
            'code' => 'Mã danh mục',
            'parent_id' => 'Danh mục cha',
            'name' => 'Tên danh mục',
            'description' => 'Mô tả',
        ],

        'table' => [
            'code' => 'Mã',
            'name' => 'Tên',
            'parent_id' => 'Danh mục cha',
            'equipments_count' => 'Số học cụ',
            'updated_at' => 'Cập nhật',
        ],
    ],

    'inventory' => [
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
            'label' => 'Duyệt phiếu',
            'success' => 'Đã duyệt phiếu và cập nhật tồn kho.',
            'error' => 'Lỗi khi duyệt phiếu.',
        ],
    ],
];