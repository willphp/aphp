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

/**
 * 自动加载
 */
class Autoloader
{
    public static function boot(): void
    {
        spl_autoload_register([new self, 'autoload']);
    }

    public function autoload(string $class): void
    {
        $file = strtr(ROOT_PATH . '/' . $class . '.php', '\\', '/');
        if (is_file($file)) include $file;
    }
}

Autoloader::boot();