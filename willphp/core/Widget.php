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
abstract class Widget
{
    protected string $tagName;
    protected string $prefix;
    protected int $expire = 0;

    public function __construct()
    {
        $path = explode('\\', get_class($this));
        $class = name_snake(end($path));
        if (!$this->tagName) {
            $this->tagName = $class;
        }
        $this->prefix = $path[1] . '@widget/' . $this->tagName . '/' . $class;
    }

    abstract public function set($id = '', array $options = []);

    public function get($id = '', array $options = [])
    {
        $name = $this->getName($id, $options);
        return Cache::make($name, fn() => $this->set($id, $options), $this->expire);
    }

    public function update(): bool
    {
        return Cache::flush($this->prefix);
    }

    public function getName($id = '', array $options = []): string
    {
        if (!empty($options)) {
            ksort($options);
            $id .= http_build_query($options);
        }
        return $this->prefix . '/' . md5(strval($id));
    }
}