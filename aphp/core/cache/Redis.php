<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core\cache;

use aphp\core\Config;

class Redis extends Base
{
    private object $redis;

    public function connect(): void
    {
        $config = Config::init()->get('cache.redis');
        $this->redis = new \Redis();
        $this->redis->connect($config['host'], $config['port']);
        if (!empty($config['pass'])) {
            $this->redis->auth($config['pass']);
        }
        $this->redis->select((int)$config['database']);
    }

    public function set(string $name, $value, int $expire = 0): bool
    {
        $name = $this->parseName($name);
        if ($this->redis->set($name, serialize($value))) {
            return ($expire > 0) ? $this->redis->expire($name, $expire) : true;
        }
        return false;
    }

    public function get(string $name, $default = null)
    {
        $name = $this->parseName($name);
        $data = $this->redis->get($name);
        return is_string($data) ? unserialize($data) : $data;
    }

    public function del(string $name): bool
    {
        $name = $this->parseName($name);
        return $this->redis->del($name);
    }

    public function has(string $name): bool
    {
        $name = $this->parseName($name);
        return (bool)$this->redis->get($name);
    }

    public function flush(string $path = ''): bool
    {
        if ($path == '*') {
            return $this->redis->flushall();
        }
        $path = empty($path) ? APP_NAME . '@cache/' : $this->parseName($path);
        $keys = $this->redis->keys($path . '*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
        return true;
    }

    private function parseName(string $name): string
    {
        [$app, $name] = parse_app_name($name);
        return rtrim($app . '@cache/' . $name, '*');
    }
}