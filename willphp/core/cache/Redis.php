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

/**
 * Redis缓存类
 */
class Redis extends Base
{
    private object $redis;

    //连接服务器
    public function connect(): void
    {
        $config = Config::init()->get('cache.redis');
        $this->redis = new \Redis();
        $this->redis->connect($config['host'], $config['port']);
        if (!empty($config['password'])) {
            $this->redis->auth($config['password']);
        }
        $this->redis->select((int)$config['database']);
    }

    //设置
    public function set(string $name, $data, int $expire = 0): bool
    {
        $name = $this->parseName($name);
        if ($this->redis->set($name, serialize($data))) {
            return ($expire > 0) ? $this->redis->expire($name, $expire) : true;
        }
        return false;
    }

    //获取
    public function get(string $name, $default = null)
    {
        $name = $this->parseName($name);
        $data = $this->redis->get($name);
        return is_string($data) ? unserialize($data) : $data;
    }

    //删除
    public function del(string $name): bool
    {
        $name = $this->parseName($name);
        return $this->redis->delete($name);
    }

    //检测存在
    public function has(string $name): bool
    {
        $name = $this->parseName($name);
        return (bool)$this->redis->get($name);
    }

    //清空
    public function flush(string $type = '[app]'): bool
    {
        //清空所有
        if ($type == '[all]') {
            return $this->redis->flushall();
        }
        //清空应用
        if ($type == '[app]' || empty($type)) {
            $type = APP_NAME . '@cache/';
        } else {
            $type = $this->parseName($type);
        }
        $keys = $this->redis->keys($type . '*');
        foreach ($keys as $key) {
            $this->redis->delete($key);
        }
        return true;
    }

    //解析缓存名
    private function parseName(string $name): string
    {
        $name = trim($name, '@');
        $app = APP_NAME;
        if (str_contains($name, '@')) {
            [$app, $name] = explode('@', $name, 2);
        }
        return rtrim($app . '@cache/' . $name, '*');
    }
}