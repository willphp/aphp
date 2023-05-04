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

class File extends Base
{
    protected string $dir;
    protected string $file;

    public function connect()
    {
        $this->dir = Dir::make(ROOT_PATH . '/runtime/session', 0777);
        $this->file = $this->dir . '/' . $this->id . '.php';
    }

    public function read(): array
    {
        return is_file($this->file) ? json_decode(file_get_contents($this->file), true) : [];
    }

    public function write()
    {
        file_put_contents($this->file, json_encode($this->items), LOCK_EX);
    }

    public function gc()
    {
        $files = glob($this->dir . '/*.php');
        foreach ($files as $file) {
            if (basename($file) != basename($this->file) && (filemtime($file) + $this->expire + 3600) < time()) {
                unlink($file);
            }
        }
    }
}