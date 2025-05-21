<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
/**
 * 过滤配置
 */
return [
    'is_filter_req' => true, // 是否过滤req参数
    'except_field' => ['markdown'], // 排除字段(可写入script脚本)
    'auto_filter' => [
        '/^(id|p)$/' => 'intval', // id分页自动转换数字
        '/^content(_\w+|\d+)?$/' => 'remove_xss', // 编辑器内容xss过滤
        '/^html(_\w+|\d+)?$/' => 'trim', // html内容
        //'markdown' => 'htmlspecialchars', // strip_tags
        //'pwd' => 'intval|md5', //演 示字段md5
        '*' => 'clear_html', // 其他处理(必须放在最后)：去除html代码
    ],
];