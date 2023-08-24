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
/**
 * 部件基类(数据缓存)
 */
abstract class Widget
{
    protected string $tagName; //缓存标签名
    protected int $expire = 0; //缓存时间(秒)
    protected string $prefix; //缓存前缀

    public function __construct()
    {
        $path = explode('\\', get_class($this));
        $class = name_snake(end($path));
        if (!$this->tagName) {
            $this->tagName = $class;
        }
        $this->prefix = $path[1] . '@widget/' . $this->tagName . '/' . $class;
    }

    //缓存数据设置
    abstract public function set($id = '', array $options = []);

    //获取
    public function get($id = '', array $options = [])
    {
        $name = $id;
        if (!empty($options)) {
            ksort($options);
            $name .= http_build_query($options);
        }
        $name = $this->prefix . '/' . md5(strval($name));
        return Cache::init()->make($name, fn() => $this->set($id, $options), $this->expire);
    }

    //更新
    public function update(): bool
    {
        return Cache::init()->flush($this->prefix);
    }
}