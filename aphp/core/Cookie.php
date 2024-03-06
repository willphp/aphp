<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
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
        $config = Config::init()->get('cookie', []);
        $this->prefix = !empty($config['prefix']) ? $config['prefix'] . '##' : APP_NAME . '##';
        $this->path = $config['path'] ?? '/';
        $this->domain = $config['domain'] ?? '';
    }

    public function set(string $name, $value, array $options = []): bool
    {
        $name = $this->prefix . $name;
        $value = Crypt::init()->encrypt(strval($value));
        $this->items[$name] = $value;
        if (PHP_SAPI != 'cli') {
            $expire = $options['expire'] ?? 0;
            if ($expire > 0) {
                $expire += time();
            }
            $path = $options['path'] ?? $this->path;
            $domain = $options['domain'] ?? $this->domain;
            setcookie($name, $value, $expire, $path, $domain);
        }
        return true;
    }

    public function get(string $name = '', $default = '')
    {
        if (empty($name)) {
            return $this->all();
        }
        if ($this->has($name)) {
            return Crypt::init()->decrypt($this->items[$this->prefix . $name]);
        }
        return $this->items[$name] ?? $default;
    }

    public function has(string $name): bool
    {
        return isset($this->items[$this->prefix . $name]);
    }

    public function del(string $name): bool
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