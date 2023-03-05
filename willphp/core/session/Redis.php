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

namespace willphp\core\session;
class Redis implements ISession
{
    use Base;

    protected static ?object $single = null;
    private object $redis;

    public function connect()
    {
        $config = get_config('session.redis');
        $this->redis = new \Redis();
        $this->redis->connect($config['host'], $config['port']);
        if (!empty($config['password'])) {
            $this->redis->auth($config['password']);
        }
        $this->redis->select((int)$config['database']);
    }

    public function read()
    {
        $data = $this->redis->get($this->sessionId);
        return $data ? json_decode($data, true) : [];
    }

    public function write()
    {
        return $this->redis->set($this->sessionId, json_encode($this->items, JSON_UNESCAPED_UNICODE));
    }

    public function gc()
    {
    }
}