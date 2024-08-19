<?php
/*------------------------------------------------------------------
 | 数据库操作类 2024-08-15 by 无念
 |------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;

use aphp\core\db\Query;

class Db
{
    private static ?object $link = null;

    public static function connect($config = [], string $table = ''): object
    {
        static $conn = [];
        $sign = empty($config) ? 'default_' . $table : md5(json_encode($config)) . '_' . $table;
        $conn[$sign] ??= Query::init($table, $config);
        return static::$link = $conn[$sign];
    }

    public static function __callStatic(string $name, array $arguments)
    {
        if (static::$link === null) {
            static::connect();
        }
        return call_user_func_array([static::$link, $name], $arguments);
    }
}