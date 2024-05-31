<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/

use aphp\core\App;

define('ROOT_PATH', strtr(realpath(__DIR__ . '/../'), '\\', '/')); // 根目录绝对路径
require ROOT_PATH . '/aphp/bootstrap.php'; // 加载框架引导文件
App::init()->boot(); // 初始化并启动应用