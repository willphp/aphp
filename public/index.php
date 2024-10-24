<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/

use aphp\core\App;

define('ROOT_PATH', strtr(realpath(__DIR__ . '/../'), '\\', '/')); // 绝对根目录
require ROOT_PATH . '/aphp/bootstrap.php'; // 引导文件
App::init()->boot(); // 初始化并启动