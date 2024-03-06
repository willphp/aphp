<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/

namespace aphp\core;
trait Single
{
    protected static object $single;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function init(): object
    {
        static $class = [];
        $args = func_get_args();
        if (empty($args)) {
            return static::$single ??= new static();
        }
        $sign = md5(serialize($args));
        $class[$sign] ??= new static(...$args);
        return static::$single = $class[$sign];
    }
}