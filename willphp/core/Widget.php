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
    protected string $table;
    protected int $expire = 0;

    abstract public function set($id = '', array $options = []);

    public function get($id = '', array $options = [])
    {
        $signId = $this->getSignId($id, $options);
        return get_cache($signId, fn() => $this->set($id, $options), $this->expire);
    }

    public function update(): bool
    {
        $type = !empty($this->table) ? 'widget/' . $this->table : 'widget';
        return Cache::driver()->flush($type);
    }

    protected function getSignId($id = '', array $options = []): string
    {
        $sign = basename(strtr(get_class($this), '\\', '/')) . $id;
        if (!empty($options)) {
            ksort($options);
            $sign .= http_build_query($options);
        }
        $type = !empty($this->table) ? 'widget/' . $this->table : 'widget';
        return $type . '.' . md5($sign);
    }
}