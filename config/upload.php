<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
/**
 * 上传配置
 */
return [
    // 文件类型
    'file_type' => [
        'image' => 'jpg|jpeg|gif|png', // 图片
        'zip' => 'zip|rar|7z', // 压缩包
        'doc' => 'doc|ppt|pdf|md|txt|sql', // 文档
        'excel' => 'xls|csv', // 电子表格
        //'audio' => 'mp3|wav', // 音频
        //'video' => 'mp4|avi', // 视频
    ],
    // 上传api类型
    'api' => [
        // 通用上传
        'file' => [
            'allow_type' => '*', // *代表文件类型中所有类型
        ],
        // 上传图片
        'image' => [
            'allow_type' => 'image', // 允许类型
            'allow_size' => 2097152, // 最大2MB
            'path' => 'image', // 上传目录
            'image_auto_cut' => false, // 图片自动裁切
        ],
        // 上传封面(自动裁切)
        'thumb' => [
            'allow_type' => 'image', // 允许后缀类型
            'allow_size' => 1048576, // 最大1MB
            'path' => 'image/thumb',
            'image_auto_cut' => true, // 图片自动裁切
            'image_cut' => [
                'type' => 6, // 裁切方式：1固宽,2固高,3固宽裁高,4固高裁宽,5缩放,6自动裁切
                'max_width' => 0, // 最大宽度
                'width' => 500, // 裁切宽度
                'height' => 310, // 裁切高度
            ],
        ],
        // 上传头像(自动裁切)
        'avatar' => [
            'allow_type' => 'image', // 允许后缀类型
            'allow_size' => 1048576, // 最大1MB
            'path' => 'image/avatar', // 上传目录
            'image_auto_cut' => true, // 图片自动裁切
            'image_cut' => [
                'type' => 6, // 裁切方式：1固宽,2固高,3固宽裁高,4固高裁宽,5缩放,6自动裁切
                'max_width' => 0, // 最大宽度
                'width' => 100, // 裁切宽度
                'height' => 100, // 裁切高度
            ],
        ],
        // 上传压缩包
        'zip' => [
            'allow_type' => 'zip', // 允许后缀类型
            'allow_size' => 10485760, // 最大10MB
            'path' => 'zip', // 上传目录
            'real_path' => 'zip', // 真实上传目录
        ],
        // 上传文档
        'doc' => [
            'allow_type' => 'doc', // 允许后缀类型
            'allow_size' => 2097152, // 最大2MB
            'path' => 'doc', // 上传目录
        ],
        // 上传电子表格
        'excel' => [
            'allow_type' => 'excel', // 允许后缀类型
            'allow_size' => 2097152, // 最大2MB
            'path' => 'excel', // 上传目录
        ],
    ],
];