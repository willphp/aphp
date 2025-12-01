<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/

use aphp\core\App;

define('ROOT_PATH', strtr(realpath(__DIR__ . '/../'), '\\', '/')); // 绝对根路径
require ROOT_PATH . '/aphp/bootstrap.php'; // 引导文件
App::init()->boot(); // 访问默认文件名(index)应用

// 可复制此文件到 任意文件.php 修改 App::init('admin') 以提升访问admin的安全性
// 域名绑定应用示例：
// www.aphp.to 默认 index
// cp.aphp.to 绑定 admin
// api.aphp.io 绑定 api
// App::init(['*' => 'index', 'cp' => 'admin', 'api.aphp.io' => 'api'])