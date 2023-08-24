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

use willphp\core\Arr;
use willphp\core\Config;
use willphp\core\Cookie;
use willphp\core\Single;

/**
 * session驱动基类
 */
abstract class Base
{
    use Single;

    protected string $name; //session 名称
    protected string $id; //session ID
    protected int $expire; //过期时间
    protected array $items; //session 数据
    protected static float $startTime; //开始时间

    private function __construct()
    {
        $this->name = Config::init()->get('session.name', 'willphp_session');
        $this->expire = Config::init()->get('session.expire', 86400);
        $this->id = $this->getId();
        $this->connect();
        $this->items = $this->read();
        self::$startTime = microtime(true);
    }

    //连接
    abstract public function connect(): void;

    //读取
    abstract public function read(): array;

    //写入
    abstract public function write(): void;

    //回收
    abstract public function gc(): void;

    //获取session id
    private function getId(): string
    {
        $id = Cookie::init()->get($this->name);
        if (!$id) {
            $id = 'willphp' . md5(microtime(true) . mt_rand(1, 6));
            Cookie::init()->set($this->name, $id, $this->expire);
        }
        return $id;
    }

    //设置
    public function set(string $name, $value = '')
    {
        return Arr::set($this->items, $name, $value);
    }

    //批量设置
    public function setBatch(array $data): void
    {
        foreach ($data as $k => $v) {
            $this->set($k, $v);
        }
    }

    //获取
    public function get(string $name = '', $default = '')
    {
        return empty($name) ? $this->items : Arr::get($this->items, $name, $default);
    }

    //获取所有
    public function all(): array
    {
        return $this->items;
    }

    //检测存在
    public function has(string $name): bool
    {
        return isset($this->items[$name]);
    }

    //删除
    public function del(string $name): bool
    {
        if (isset($this->items[$name])) {
            unset($this->items[$name]);
        }
        return true;
    }

    //清空
    public function flush(): bool
    {
        $this->items = [];
        return true;
    }

    //闪存快捷操作方法
    public function flash($name = '', $value = '')
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

    //清除闪存
    public function clearFlash(): void
    {
        $flash = $this->items['_FLASH_'] ?? [];
        foreach ($flash as $name => $val) {
            if ($val[1] != self::$startTime) {
                unset($this->items['_FLASH_'][$name]);
            }
        }
    }

    //关闭
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