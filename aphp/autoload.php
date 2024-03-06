<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
defined('APHP_TOP') or die('Access Denied');

class Autoloader
{
    public static function boot(): void
    {
        spl_autoload_register([new self, 'autoload']);
    }

    public function autoload(string $class): void
    {
        $file = strtr(APHP_TOP . '/' . $class . '.php', '\\', '/');
        if (is_file($file)) include $file;
    }
}

Autoloader::boot();