<?php
/*------------------------------------------------------------------
 | 路由配置 2024-08-15 by 无念
 |------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
return [
    'default_controller' => 'index', // 默认控制器
    'default_action' => 'index', // 默认方法
    'url_auto_suffix' => '.html', // url函数自动后缀
    'url_clear_suffix' => ['.html'], // url地址去除后缀
    'rule_alias' => [
        ':any' => '.*', // any
        ':num' => '[0-9\-]+', // number
        ':page' => '[0-9]+', // page
        ':float' => '[0-9\.\-]+', // float
        ':string' => '[a-zA-Z0-9\-_]+', // string
        ':alpha' => '[a-zA-Z\x7f-\xff0-9-_]+', // alpha
    ],
    'empty_to' => [
        'class' => 'app\\index\\controller\\Index', // 空控制器
        'action' => 'index', // 空方法
        'params' => 'route', // 空参数
    ],
];