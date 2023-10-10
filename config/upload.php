<?php
/*----------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: 113344.com
 |----------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2023, 113344.com. All Rights Reserved.
 |---------------------------------------------------------------*/
return [
    //图片上传
    'img' => [
        'allow_ext' => ['jpg', 'jpeg', 'gif', 'png'], //允许的文件扩展名
        'allow_size' => 2097152, //最大上传大小2MB
        'path' => 'public/uploads/'.date('Y/md'),
        'auto_thumb' => true, //自动生成thumb
        'thumb' => [
            'thumb_type' => 1, //生成方式：1固宽,2固高,3固宽裁高,4固高裁宽,5缩放,6自动裁切
            'max_width' => 980, //当图片宽度超过750时生成thumb
            'width' => 0, //thumb宽度
            'height' => 0, //thumb高度
            'del_src' => true, //生成thumb后删除源图片
        ],
    ],
    //封面上传
    'thumb' => [
        'allow_ext' => ['jpg', 'jpeg', 'gif', 'png'], //允许的文件扩展名
        'allow_size' => 1048576, //最大上传大小1MB
        'path' => 'public/uploads/thumb',
        'auto_thumb' => true, //自动生成thumb
        'thumb' => [
            'thumb_type' => 6, //生成方式：1固宽,2固高,3固宽裁高,4固高裁宽,5缩放,6自动裁切
            'max_width' => 0, //当图片宽度超过750时生成thumb
            'width' => 180, //thumb宽度
            'height' => 120, //thumb高度
            'del_src' => true, //生成thumb后删除源图片
        ],
    ],
    //头像上传
    'avatar' => [
        'allow_ext' => ['jpg', 'jpeg', 'gif', 'png'], //允许的文件扩展名
        'allow_size' => 1048576, //最大上传大小1MB
        'path' => 'public/uploads/avatar',
        'auto_thumb' => true, //自动生成thumb
        'thumb' => [
            'thumb_type' => 6, //生成方式：1固宽,2固高,3固宽裁高,4固高裁宽,5缩放,6自动裁切
            'max_width' => 0, //当图片宽度超过750时生成thumb
            'width' => 100, //thumb宽度
            'height' => 100, //thumb高度
            'del_src' => true, //生成thumb后删除源图片
        ],
    ],
    //压缩文件上传
    'zip' => [
        'allow_ext' => ['zip', 'rar'], //允许的文件扩展名
        'allow_size' => 2097152, //最大上传大小2MB
        'path' => 'public/uploads/zip',
    ],
    //文档上传
    'doc' => [
        'allow_ext' => ['doc', 'txt'], //允许的文件扩展名
        'allow_size' => 2097152, //最大上传大小2MB
        'path' => 'public/uploads/doc',
    ],
];