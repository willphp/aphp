<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
return [
    'auto_filter_req' => true, //自动过滤req参数
    'except_key' => [], //排除主键(可写入script脚本)
    'auto' => [
        '/^(id|p)$/' => 'intval', //id分页自动转换数字
        '/^content(_\w+|\d+)?$/' => 'remove_xss', //html内容xss过滤
        'pwd' => 'intval|md5', //演示字段md5
        '*' => 'clear_html', //其他处理(必须放在最后)：去除html代码
    ],
];