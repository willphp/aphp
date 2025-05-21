<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
/**
 * 分页配置
 */
return [
    'page_var' => 'p', // 分页$_GET变量
    'unset_get_var' => ['csrf_token','_path'], // 不需要保留的get参数
    'page_size' => 10, // 每页显示数量
    'show_num' => 5, // 页面显示页码数量
    'options' => [
        'home' => '首页',
        'end' => '尾页',
        'up' => '上页',
        'down' => '下页',
        'pre' => '上n',
        'next' => '下n',
        'header' => '条记录',
        'unit' => '页',
        'theme' => 0,
    ],
    'page_html' => '%home% %up% %pre% %number% %next% %down% %end%', //显示的html
];