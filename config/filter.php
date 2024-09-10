<?php
/*------------------------------------------------------------------
 | 输入过滤配置 2024-08-15 by 无念
 |------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
return [
    'auto_filter_req' => true, // 自动过滤req参数
    'except_field' => ['content'], // 排除字段(可写入script脚本)
    'auto' => [
        'markdown' => 'htmlspecialchars', // strip_tags
        '/^(id|p)$/' => 'intval', // id分页自动转换数字
        '/^content(_\w+|\d+)?$/' => 'remove_xss', // html内容xss过滤
        'pwd' => 'intval|md5', //演 示字段md5
        '*' => 'clear_html', // 其他处理(必须放在最后)：去除html代码
    ],
];