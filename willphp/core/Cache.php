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
/**
 * 缓存调用类
 */
class Cache
{
    protected static ?object $link = null; //驱动链接

    //初始化缓存驱动
    public static function init(?string $driver = null): object
    {
        static $cache = [];
        $driver ??= Config::init()->get('cache.driver', 'file');
        if (!isset($cache[$driver])) {
            $class = '\\willphp\\core\\cache\\' . ucfirst($driver);
            $cache[$driver] = call_user_func([$class, 'init']);
        }
        return static::$link = $cache[$driver];
    }

    //静态调用方法
    public static function __callStatic(string $name, array $arguments)
    {
        if (is_null(static::$link)) {
            static::init();
        }
        return call_user_func_array([static::$link, $name], $arguments);
    }
}