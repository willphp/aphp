<?php
/*------------------------------------------------------------------
 | 类自动加载 2024-08-13 by 无念
 |------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
defined('ROOT_PATH') or die('Access Denied');

class Autoloader
{
    public static function boot(): void
    {
        spl_autoload_register([new self, 'autoload']); // 注册自动加载
    }

    public function autoload(string $class): void
    {
        $file = strtr(ROOT_PATH . '/' . $class . '.php', '\\', '/'); // 路径
        if (is_file($file)) {
            include $file; // 加载
        }
    }
}

Autoloader::boot(); // 启动自动加载