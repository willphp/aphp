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
trait Single
{
    protected static ?object $single = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function init(): object
    {
        $args = func_get_args();
        if (empty($args)) {
            return static::$single ??= new static();
        }
        static $class = [];
        $sign = md5(serialize($args));
        $class[$sign] ??= new static(...$args);
        return static::$single = $class[$sign];
    }
}