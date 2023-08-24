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
 * session调用处理类
 */
class Session
{
    protected static ?object $link = null; //驱动链接

    //初始化处理驱动
    public static function init(): object
    {
        if (is_null(static::$link)) {
            $driver = Config::init()->get('session.driver', 'file');
            $class = '\\willphp\\core\\session\\' . ucfirst($driver);
            static::$link = call_user_func([$class, 'init']);
        }
        return static::$link;
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