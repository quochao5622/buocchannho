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
];
