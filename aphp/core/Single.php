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
 * 单例调用类
 */
trait Single
{
    protected static object $single; // 单例实例

    // 禁止实例化
    private function __construct()
    {
    }

    // 禁止克隆
    private function __clone()
    {
    }

    // 获取单例实例，不存在创建
    public static function init(...$args): static
    {
        static $class = [];
        if (empty($args)) {
            return static::$single ??= new static(); // 无参数时直接返回已有实例
        }
        $sign = md5(serialize($args));
        $class[$sign] ??= new static(...$args);
        return static::$single = $class[$sign];
    }
}