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
    //全局中间件
    'global' => [
        //\middleware\Boot::class, //框架启动
    ],
    //控制器中间件(可自行定义)
    'controller' => [
        'auth' => [
            \middleware\controller\Auth::class, //权限检测
        ],
        'test' => [
            \middleware\controller\Test::class, //测试中间件
        ],
    ],
    //应用中间件(框架内置)
    'web' => [
        'database_query' => [],
        'database_execute' => [],
        'controller_start' => [],
    ],
];