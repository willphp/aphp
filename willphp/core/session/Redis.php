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

use willphp\core\Config;

class Redis extends Base
{
    protected object $redis;

    public function connect()
    {
        $config = Config::init()->get('session.redis');
        $this->redis = new \Redis();
        $this->redis->connect($config['host'], $config['port']);
        if (!empty($config['password'])) {
            $this->redis->auth($config['password']);
        }
        $this->redis->select((int)$config['database']);
    }

    public function read(): array
    {
        $data = $this->redis->get($this->id);
        return $data ? json_decode($data, true) : [];
    }

    public function write()
    {
        $this->redis->set($this->id, json_encode($this->items, JSON_UNESCAPED_UNICODE));
    }

    public function gc()
    {
    }
}