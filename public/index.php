<?php
/*----------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: 113344.com
 |----------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2023, 113344.com. All Rights Reserved.
 |---------------------------------------------------------------*/
declare(strict_types=1);
use willphp\core\App;
header('Content-type: text/html; charset=utf-8');
date_default_timezone_set('PRC');
define('ROOT_PATH', strtr(realpath(__DIR__ . '/../'), '\\', '/'));
require ROOT_PATH . '/willphp/bootstrap.php';
App::init('index')->boot();