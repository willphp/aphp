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

use Exception;

class File extends Base
{
    protected static ?object $single = null;
    private string $dir;

    public function dir(string $dir): object
    {
        if (!dir_create($dir)) throw new Exception('缓存目录创建失败或不可写');
        $this->dir = $dir;
        return $this;
    }

    public function connect()
    {
        $dir = get_config('cache.file.path', 'cache');
        $this->dir(RUNTIME_PATH . '/' . $dir);
    }

    public function set(string $name, $data, int $expire = 0): bool
    {
        $file = $this->getFile($name);
        $content = sprintf("%010d", $expire) . json_encode($data);
        return (bool)file_put_contents($file, $content);
    }

    public function get(string $name, $default = null)
    {
        $file = $this->getFile($name);
        if (!is_file($file) || !is_writable($file)) return $default;
        $content = file_get_contents($file);
        $expire = intval(substr($content, 0, 10));
        if ($expire > 0 && filemtime($file) + $expire < time()) {
            unlink($file);
            return $default;
        }
        return json_decode(substr($content, 10), true);
    }

    public function del(string $name): bool
    {
        $file = $this->getFile($name);
        return !is_file($file) or unlink($file);
    }

    public function has(string $name): bool
    {
        return (bool)$this->get($name);
    }

    public function flush(string $type = ''): bool
    {
        $type = !empty($type) ? '/' . $type : '';
        return dir_del($this->dir . $type);
    }

    private function getFile(string $name): string
    {
        $dir = $this->dir;
        if (str_contains($name, '.')) {
            [$type, $name] = explode('.', $name);
            $dir .= '/' . $type;
            if (!is_dir($dir)) mkdir($dir, 0755, true);
        }
        return $dir . '/' . md5($name) . '.php';
    }
}