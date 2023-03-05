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
    'driver' => 'file', //默认驱动
    'name' => 'session_id', //名称
    'domain' => '', //有效域名
    'expire' => 86400 * 10, //过期时间
    'file' => [
        'path' => 'session', //文件类session保存路径
    ],
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'database' => 0,
    ],
];