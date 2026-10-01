<?php

// Chỉ ghi đè các key cần thiết; phần còn lại lấy từ config mặc định của Livewire (mergeConfigFrom gộp theo key cấp 1)
return [

    'payload' => [
        'max_size' => 1024 * 1024,   // 1MB - maximum request payload size in bytes
        'max_nesting_depth' => 10,   // Maximum depth of dot-notation property paths
        'max_calls' => 150,          // Maximum method calls per request (mặc định 50, tăng vì các call bị dồn khi request chậm)
        'max_components' => 200,     // Maximum components per batch request
    ],

];
