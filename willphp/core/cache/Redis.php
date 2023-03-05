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
class Redis extends Base
{
    protected static ?object $single = null;
    private object $redis;

    public function connect()
    {
        $config = get_config('cache.redis');
        $this->redis = new \Redis();
        $this->redis->connect($config['host'], $config['port']);
        if (!empty($config['password'])) {
            $this->redis->auth($config['password']);
        }
        $this->redis->select((int)$config['database']);
    }

    public function set(string $name, $data, int $expire = 0): bool
    {
        $data = serialize($data);
        if ($this->redis->set($name, $data)) {
            return ($expire > 0) ? $this->redis->expire($name, $expire) : true;
        }
        return false;
    }

    public function get(string $name, $default = null)
    {
        $data = $this->redis->get($name);
        return is_string($data) ? unserialize($data) : $data;
    }

    public function del(string $name): bool
    {
        return $this->redis->delete($name);
    }

    public function has(string $name): bool
    {
        return (bool)$this->redis->get($name);
    }

    public function flush(string $type = ''): bool
    {
        return $this->redis->flushall();
    }
}