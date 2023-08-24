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
    //控制器中间件
    'controller' => [
        'auth' => [
            \middleware\controller\Auth::class, //权限检测
        ],
        'test' => [
            \middleware\controller\Test::class, //测试
            \middleware\controller\Run::class, //运行
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