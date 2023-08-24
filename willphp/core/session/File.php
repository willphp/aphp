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

use willphp\core\Dir;

/**
 * session文件驱动处理类
 */
class File extends Base
{
    protected string $dir; //存放目录
    protected string $file; //文件

    //连接
    public function connect(): void
    {
        $this->dir = Dir::make(ROOT_PATH . '/runtime/session', 0777);
        $this->file = $this->dir . '/' . $this->id . '.php';
    }

    //读取
    public function read(): array
    {
        return is_file($this->file) ? json_decode(file_get_contents($this->file), true) : [];
    }

    //写入
    public function write(): void
    {
        file_put_contents($this->file, json_encode($this->items), LOCK_EX);
    }

    //回收
    public function gc(): void
    {
        $files = glob($this->dir . '/*.php');
        foreach ($files as $file) {
            if (basename($file) != basename($this->file) && (filemtime($file) + $this->expire + 3600) < time()) {
                unlink($file);
            }
        }
    }
}