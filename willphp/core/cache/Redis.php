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

use willphp\core\Config;

class Redis extends Base
{
    private object $redis;

    public function connect()
    {
        $config = Config::init()->get('cache.redis');
        $this->redis = new \Redis();
        $this->redis->connect($config['host'], $config['port']);
        if (!empty($config['password'])) {
            $this->redis->auth($config['password']);
        }
        $this->redis->select((int)$config['database']);
    }

    public function set(string $name, $data, int $expire = 0): bool
    {
        $name = $this->getName($name);
        if ($this->redis->set($name, serialize($data))) {
            return ($expire > 0) ? $this->redis->expire($name, $expire) : true;
        }
        return false;
    }

    public function get(string $name, $default = null)
    {
        $name = $this->getName($name);
        $data = $this->redis->get($name);
        return is_string($data) ? unserialize($data) : $data;
    }

    public function del(string $name): bool
    {
        $name = $this->getName($name);
        return $this->redis->delete($name);
    }

    public function has(string $name): bool
    {
        $name = $this->getName($name);
        return (bool)$this->redis->get($name);
    }

    public function flush(string $prefix = '[app]'): bool
    {
        if ($prefix == '[all]') {
            return $this->redis->flushall();
        }
        if ($prefix == '[app]' || empty($prefix)) {
            $prefix = APP_NAME . '@cache/';
        } else {
            $prefix = $this->getName($prefix);
        }
        $keys = $this->redis->keys($prefix . '*');
        foreach ($keys as $key) {
            $this->redis->delete($key);
        }
        return true;
    }

    private function getName(string $name): string
    {
        [$app, $name] = pre_split($name, APP_NAME, '@');
        return rtrim($app . '@cache/' . $name, '*');
    }
}