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

namespace willphp\core\cache;

use Closure;
use willphp\core\Single;

abstract class Base
{
    use Single;

    private function __construct()
    {
        $this->connect();
    }

    abstract public function connect();

    abstract public function set(string $name, $data, int $expire = 0): bool;

    abstract public function get(string $name, $default = null);

    abstract public function del(string $name): bool;

    abstract public function has(string $name): bool;

    abstract public function flush(string $type = ''): bool;

    public function getCache(string $name, ?Closure $closure = null, int $expire = 0)
    {
        $data = $this->get($name);
        if (empty($data) && $closure instanceof Closure) {
            $data = $closure();
            $this->set($name, $data, $expire);
        }
        return $data;
    }
}