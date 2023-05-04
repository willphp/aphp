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

namespace willphp\core;
class Cookie
{
    use Single;

    protected array $items;
    private string $prefix;
    private string $path;
    private string $domain;

    private function __construct()
    {
        $this->items = $_COOKIE;
        $this->prefix = APP_NAME . '##';
        $this->path = Config::init()->get('cookie.path', '/');
        $this->domain = Config::init()->get('cookie.domain', '');
    }

    public function set(string $name, $value, int $expire = 0, ?string $path = null, ?string $domain = null): void
    {
        $name = $this->prefix . $name;
        $value = Crypt::init()->encrypt(strval($value));
        $this->items[$name] = $value;
        if (PHP_SAPI != 'cli') {
            if ($expire > 0) {
                $expire += time();
            }
            $path ??= $this->path;
            $domain ??= $this->domain;
            setcookie($name, $value, $expire, $path, $domain);
        }
    }

    public function get(string $name, $default = '')
    {
        if ($this->has($name)) {
            return Crypt::init()->decrypt($this->items[$this->prefix . $name]);
        }
        return $this->items[$name] ?? $default;
    }

    public function has($name): bool
    {
        return isset($this->items[$this->prefix . $name]);
    }

    public function del($name): bool
    {
        if (isset($this->items[$this->prefix . $name])) {
            unset($this->items[$this->prefix . $name]);
        }
        if (PHP_SAPI != 'cli') {
            setcookie($this->prefix . $name, '', 1);
        }
        return true;
    }

    public function flush(): bool
    {
        if (PHP_SAPI != 'cli') {
            $list = array_keys($this->items);
            foreach ($list as $name) {
                setcookie($name, '', 1, '/');
            }
        }
        $this->items = [];
        return true;
    }

    public function all(): array
    {
        $data = [];
        foreach ($this->items as $name => $value) {
            if (str_starts_with($name, $this->prefix)) {
                $name = substr($name, strlen($this->prefix));
                $value = Crypt::init()->decrypt($value);
            }
            $data[$name] = $value;
        }
        return $data;
    }
}