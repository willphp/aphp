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
    'prefix' => 'cache#', //名称前缀
    'file' => [
        'path' => 'cache', //文件类cache保存路径
    ],
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '123456',
        'database' => 0,
    ],
];