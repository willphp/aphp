<?php
/*----------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: 113344.com
 |----------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2023, 113344.com. All Rights Reserved.
 |---------------------------------------------------------------*/
declare(strict_types=1); //采用严格模式
namespace willphp\core; //设置命名空间
header('Content-type: text/html; charset=utf-8'); //设置编码
date_default_timezone_set('PRC'); //设置时区
define('ROOT_PATH', strtr(realpath(__DIR__ . '/../'), '\\', '/')); //框架绝对根路径
require ROOT_PATH . '/willphp/bootstrap.php'; //载入引导文件
App::init()->boot(); //应用单例初始化->启动