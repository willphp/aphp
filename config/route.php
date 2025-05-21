<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
/**
 * 路由配置
 */
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
        ':keyword' => '[a-zA-Z\x7f-\xff0-9-%\+]+', // keyword
    ],
    'is_empty_jump' => false, // 是否开启空跳转
    'jump_to' => [
        'class' => 'app\\index\\controller\\Error', // 空控制器
        'action' => 'empty', // 空方法
        'params' => '_path', // 参数-原路径参数
    ],
    'is_auto_rewrite' => true, // 是否开启自动重写
    'auto_rewrite_rule' => [
        '([a-zA-Z]+)_([0-9]+)' => '${1}/index/p/${2}',
        '([a-zA-Z]+)/([a-zA-Z]+)_([0-9]+)' => '${1}/${2}/id/${3}',
    ],
];