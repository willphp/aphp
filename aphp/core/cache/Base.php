<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core\cache;

use aphp\core\Single;
use Closure;

abstract class Base
{
    use Single;

    private function __construct()
    {
        $this->connect();
    }

    public function make(string $name, ?Closure $closure = null, int $expire = 0)
    {
        $data = $this->get($name);
        if (empty($data) && $closure instanceof Closure) {
            $data = $closure();
            $this->set($name, $data, $expire);
        }
        return $data;
    }

    abstract public function connect();

    abstract public function set(string $name, $value, int $expire = 0): bool;

    abstract public function get(string $name, $default = null);

    abstract public function del(string $name): bool;

    abstract public function has(string $name): bool;

    abstract public function flush(string $path = ''): bool;
}