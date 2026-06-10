<?php

return [
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

    'validation' => [
        'unique' => 'Tên danh mục này đã tồn tại.',
    ],
];
