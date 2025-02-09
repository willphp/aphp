<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core\session;

use aphp\core\Tool;

/**
 * 文件驱动Session类
 */
class File extends Base
{
    protected string $dir;
    protected string $file;

    public function connect(): void
    {
        $this->dir = Tool::dir_init(ROOT_PATH . '/runtime/session', 0777);
        $this->file = $this->dir . '/' . $this->session_id . '.php';
    }

    public function read(): array
    {
        return is_file($this->file) ? json_decode(file_get_contents($this->file), true) : [];
    }

    public function write(): void
    {
        file_put_contents($this->file, json_encode($this->items), LOCK_EX);
    }

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