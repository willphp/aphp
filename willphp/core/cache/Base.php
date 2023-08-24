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

namespace willphp\core\cache;

use Closure;
use willphp\core\Single;

/**
 * 缓存基类
 */
abstract class Base
{
    use Single;

    private function __construct()
    {
        $this->connect();
    }

    //连接
    abstract public function connect();

    //设置
    abstract public function set(string $name, $data, int $expire = 0): bool;

    //获取
    abstract public function get(string $name, $default = null);

    //删除
    abstract public function del(string $name): bool;

    //检测存在
    abstract public function has(string $name): bool;

    //清空
    abstract public function flush(string $type = '[app]'): bool;

    //调用，不存在时生成
    public function make(string $name, ?Closure $closure = null, int $expire = 0)
    {
        $data = $this->get($name);
        if (empty($data) && $closure instanceof Closure) {
            $data = $closure();
            $this->set($name, $data, $expire);
        }
        return $data;
    }
}