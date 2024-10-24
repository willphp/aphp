<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
/**
 * Session配置
 */
return [
    'driver' => 'file', // 支持file,redis驱动
    'name' => 'aphp_session',
    'expire' => 86400, //1 day
    'domain'   => '',
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'pass' => '',
        'database' => 0,
    ],
];