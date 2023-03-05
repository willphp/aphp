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

use willphp\core\Single;

trait Base
{
    use Single;

    protected string $sessionId; //session ID
    protected string $sessionName; //session 名称
    protected int $expire; //过期时间
    protected array $items; //session 数据
    protected static ?float $startTime = null;

    private function __construct()
    {
        $this->sessionName = get_config('session.name', 'willphp');
        $this->expire = get_config('session.expire', 864000);
        $this->sessionId = $this->getSessionId();
        $this->connect();
        $this->items = $this->read();
        if (is_null(self::$startTime)) {
            self::$startTime = microtime(true);
        }
    }

    final protected function getSessionId(): string
    {
        $id = cookie($this->sessionName);
        if (!$id) {
            $id = 'willphp' . md5(microtime(true) . mt_rand(1, 6));
            cookie($this->sessionName, $id, $this->expire);
        }
        return $id;
    }

    public function set(string $key, $value = '')
    {
        return array_dot_set($this->items, $key, $value);
    }

    public function batchSet(array $data): void
    {
        foreach ($data as $k => $v) {
            $this->set($k, $v);
        }
    }

    public function get(string $key = '', $default = '')
    {
        return empty($key) ? $this->items : array_dot_get($this->items, $key, $default);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function has($name): bool
    {
        return isset($this->items[$name]);
    }

    public function del($name): bool
    {
        if (isset($this->items[$name])) {
            unset($this->items[$name]);
        }
        return true;
    }

    public function flush(): bool
    {
        $this->items = [];
        return true;
    }

    public function close(): void
    {
        $this->write();
        if (mt_rand(1, 100) == 1) {
            $this->gc();
        }
    }

    public function flash($name = '', $value = '')
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->set('_FLASH_.' . $key, [$val, self::$startTime]);
            }
            return true;
        } elseif ($name === '') {
            return $this->get('_FLASH_', []);
        } elseif (is_null($name)) {
            return $this->del('_FLASH_');
        }
        if (is_null($value)) {
            if (isset($this->items['_FLASH_'][$name])) {
                unset($this->items['_FLASH_'][$name]);
            }
        } elseif ($value === '') {
            $data = $this->get('_FLASH_.' . $name);
            return $data[0] ?? '';
        }
        return $this->set('_FLASH_.' . $name, [$value, self::$startTime]);
    }

    public function clearFlash(): void
    {
        $flash = $this->flash();
        foreach ($flash as $k => $v) {
            if ($v[1] != self::$startTime) {
                $this->flash($k, null);
            }
        }
    }

    public function __destruct()
    {
        $this->clearFlash();
        $this->close();
    }
}
