<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core\session;

use aphp\core\Config;
use aphp\core\Cookie;
use aphp\core\Single;
use aphp\core\Tool;

abstract class Base
{
    use Single;

    protected string $session_name;
    protected int $expire;
    protected string $session_id;
    protected array $items = [];
    protected static float $startTime;

    private function __construct()
    {
        $this->session_name = Config::init()->get('session.name', 'aphp_session');
        $this->expire = Config::init()->get('session.expire', 86400);
        $this->session_id = $this->getSessionId();
        $this->connect();
        $this->items = $this->read();
        self::$startTime = microtime(true);
    }

    abstract public function connect(): void;

    abstract public function read(): array;

    abstract public function write(): void;

    abstract public function gc(): void;

    private function getSessionId(): string
    {
        $id = Cookie::init()->get($this->session_name);
        if (!$id) {
            $id = 'aphp' . md5(microtime(true) . mt_rand(1, 6));
            Cookie::init()->set($this->session_name, $id, ['expire' => $this->expire, 'domain' => Config::init()->get('session.domain')]);
        }
        return $id;
    }

    public function set(string $name, $value = '')
    {
        return Tool::arr_set($this->items, $name, $value);
    }

    public function setBatch(array $data): void
    {
        foreach ($data as $k => $v) {
            $this->set($k, $v);
        }
    }

    public function get(string $name = '', $default = '')
    {
        return empty($name) ? $this->items : Tool::arr_get($this->items, $name, $default);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function has(string $name): bool
    {
        return isset($this->items[$name]);
    }

    public function del(string $name): bool
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

    public function flash(string $name = '', $value = '')
    {
        if ($name === '') {
            return $this->get('_FLASH_', []);
        }
        if (is_null($name)) {
            return $this->del('_FLASH_');
        }
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->set('_FLASH_.' . $key, [$val, self::$startTime]);
            }
            return true;
        }
        if ($value === '') {
            $data = $this->get('_FLASH_.' . $name);
            return $data[0] ?? '';
        }
        if (is_null($value)) {
            if (isset($this->items['_FLASH_'][$name])) {
                unset($this->items['_FLASH_'][$name]);
            }
            return true;
        }
        return $this->set('_FLASH_.' . $name, [$value, self::$startTime]);
    }

    public function clearFlash(): void
    {
        $flash = $this->items['_FLASH_'] ?? [];
        foreach ($flash as $name => $val) {
            if ($val[1] != self::$startTime) {
                unset($this->items['_FLASH_'][$name]);
            }
        }
    }

    public function close(): void
    {
        $this->write();
        if (mt_rand(1, 100) == 1) {
            $this->gc();
        }
    }

    public function __destruct()
    {
        $this->clearFlash();
        $this->close();
    }
}