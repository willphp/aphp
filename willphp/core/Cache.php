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

namespace willphp\core;

class Cache
{
    protected static ?object $link = null;

    public static function driver(?string $driver = null): object
    {
        static $cache = [];
        $driver ??= get_config('cache.driver', 'file');
        if (!isset($cache[$driver])) {
            $class = '\\willphp\\core\\cache\\' . ucfirst($driver);
            $cache[$driver] = call_user_func([$class, 'init']);
        }
        return static::$link = $cache[$driver];
    }

    public static function __callStatic(string $name, array $arguments)
    {
        if (is_null(static::$link)) {
            static::driver();
        }
        return call_user_func_array([static::$link, $name], $arguments);
    }
}