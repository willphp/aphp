<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
/**
 * 中间件配置
 */
return [
    // 控制器中间件
    'controller' => [
        'auth' => [
            \middleware\controller\Auth::class, // 登录验证
        ],
        'test' => [
            \middleware\controller\Test::class, // 测试示例
        ],
    ],
    // 全局中间件
    'common' => [
        \middleware\Boot::class, // 框架启动
    ],
    // 框架中间件
    'framework' => [
        'controller_start' => [
           // \middleware\Csrf::class, // 表单令牌验证
        ], // 控制器开始
        'database_query' => [], // 查询sql
        'database_execute' => [], // 执行sql
    ],
];