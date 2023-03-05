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

use willphp\core\db\Query;

class Db
{
    private static ?object $link = null;

    public static function connect($config = [], string $table = ''): object
    {
        static $conn = [];
        $sign = empty($config) ? 'default_' . $table : md5(serialize($config)) . '_' . $table;
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