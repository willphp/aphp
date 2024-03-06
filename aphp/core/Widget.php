<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
abstract class Widget
{
    protected string $tag;
    protected int $expire = 0;
    protected string $prefix;

    public function __construct()
    {
        $path = explode('\\', get_class($this));
        $class = name_snake(end($path));
        if (!$this->tag) {
            $this->tag = $class;
        }
        $this->prefix = $path[1] . '@widget/' . $this->tag . '/' . $class;
    }

    abstract public function set($id = '', array $options = []);

    public function get($id = '', array $options = [])
    {
        $name = $id;
        if (!empty($options)) {
            ksort($options);
            $name .= http_build_query($options);
        }
        $name = empty($name) ? $this->prefix . '/default' : $this->prefix . '/' . md5(strval($name));
        return Cache::init()->make($name, fn() => $this->set($id, $options), $this->expire);
    }

    public function refresh(): bool
    {
        return Cache::init()->flush($this->prefix . '/*');
    }
}