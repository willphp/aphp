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
class File implements ISession
{
    use Base;

    protected static ?object $single = null;
    protected string $dir;
    protected string $file;

    public function connect(): void
    {
        $dir = RUNTIME_PATH . '/' . get_config('session.file.path', 'session');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->dir = $dir;
        $this->file = $this->dir . '/' . $this->sessionId . '.php';
    }

    public function read(): array
    {
        return is_file($this->file) ? json_decode(file_get_contents($this->file), true) : [];
    }

    public function write()
    {
        return file_put_contents($this->file, json_encode($this->items), LOCK_EX);
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