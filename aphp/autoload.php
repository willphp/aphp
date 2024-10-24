<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);
defined('ROOT_PATH') or die('Access Denied'); // 检测常量

/**
 * 自动加载器
 */
class Autoloader
{
    public static function boot(): void
    {
        spl_autoload_register([new self, 'autoload']); // 注册自动加载
    }

    public function autoload(string $class): void
    {
        $file = strtr(ROOT_PATH . '/' . $class . '.php', '\\', '/');
        if (is_file($file)) {
            include $file; // 加载文件
        }
    }
}

Autoloader::boot(); // 启动加载器