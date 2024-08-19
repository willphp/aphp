<?php
/*------------------------------------------------------------------
 | 入口文件 2024-08-13 by 无念
 |------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
use aphp\core\App;
define('ROOT_PATH', strtr(realpath(__DIR__ . '/../'), '\\', '/')); // 绝对根目录
require ROOT_PATH . '/aphp/bootstrap.php'; // 引导文件
App::init()->boot(); // 初始化并启动