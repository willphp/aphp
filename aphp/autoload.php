<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
defined('ROOT_PATH') or die('Access Denied');

class Autoloader
{
    public static function boot(): void
    {
        spl_autoload_register([new self, 'autoload']); // 注册自动加载函数
    }

    public function autoload(string $class): void
    {
        $file = strtr(ROOT_PATH . '/' . $class . '.php', '\\', '/'); // 文件路径
        if (is_file($file)) {
            include $file; // 加载文件
        }
    }
}

Autoloader::boot(); // 启动自动加载