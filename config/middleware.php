<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
return [
    //控制器中间件
    'controller' => [
        'auth' => [
            \middleware\controller\Auth::class, //登录验证
        ],
        'rbac' => [
            \middleware\controller\Rbac::class, //后台验证
        ],
    ],
    //全局中间件
    'common' => [
        \middleware\Boot::class, //框架启动
    ],
    //框架中间件
    'framework' => [
        'controller_start' => [], //控制器开始
        'database_query' => [], //数据库查询sql
        'database_execute' => [], //数据库执行sql
    ],
];