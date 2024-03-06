<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
return [
    'driver' => 'file', //file,redis
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