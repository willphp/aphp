<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
return [
    'page_var' => 'p', //分页$_GET变量
    'page_size' => 10, //每页显示数量
    'show_num' => 5, //页面显示页码数量
    'options' => [
        'home' => '首页',
        'end' => '尾页',
        'up' => '上一页',
        'down' => '下一页',
        'pre' => '上n页',
        'next' => '下n页',
        'header' => '条记录',
        'unit' => '页',
        'theme' => 0,
    ],
    'page_html' => '[%total% %header%] [%current%/%pages% %unit%] %home% %up% %pre% %number% %next% %down% %end%', //显示的html
];