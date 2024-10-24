<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
/**
 * Session类
 */
class Session
{
    protected static ?object $link = null;

    public static function init(): object
    {
        if (is_null(static::$link)) {
            $driver = Config::init()->get('session.driver', 'file');
            $class = '\\aphp\\core\\session\\' . ucfirst($driver);
            static::$link = call_user_func([$class, 'init']);
        }
        return static::$link;
    }

    public static function __callStatic(string $name, array $arguments)
    {
        if (is_null(static::$link)) {
            static::init();
        }
        return call_user_func_array([static::$link, $name], $arguments);
    }
}