<?php
/*------------------------------------------------------------------
 | 缓存配置 2024/9/10 0010  by 无念
 |------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
return [
    'driver' => 'file', // 支持file,redis驱动
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'pass' => '123456',
        'database' => 0,
    ],
];