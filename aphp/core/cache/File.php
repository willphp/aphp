<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core\cache;
/**
 * 文件缓存类
 */
class File extends Base
{
    public function connect(): void
    {
    }

    public function set(string $name, mixed $value, int $expire = 0): bool
    {
        $file = $this->parseName($name, true);
        $content = sprintf("%010d", $expire) . json_encode($value);
        return (bool)file_put_contents($file, $content);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        $file = $this->parseName($name);
        if (!is_file($file) || !is_writable($file)) {
            return $default;
        }
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
        $file = $this->parseName($name);
        return !is_file($file) || unlink($file);
    }

    public function has(string $name): bool
    {
        return (bool)$this->get($name);
    }

    public function flush(string $path = ''): bool
    {
        // 清除当前应用
        if (empty($path)) {
            return dir_delete(ROOT_PATH . '/runtime/' . APP_NAME . '/cache');
        }
        // 清除所有应用
        if ($path == '*') {
            $dirs = glob(ROOT_PATH . '/runtime/*', GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                dir_delete($dir . '/cache');
            }
            return true;
        }
        // 清除指定应用中指定标识
        [$app, $path] = name_parse($path, APP_NAME);
        if ($path == '*') {
            return dir_delete(ROOT_PATH . '/runtime/' . $app . '/cache');
        }
        $path = rtrim($path, '*');
        return dir_delete(ROOT_PATH . '/runtime/' . $app . '/cache/' . $path, true);
    }

    private function parseName(string $name, bool $dirMake = false): string
    {
        [$app, $name] = name_parse($name, APP_NAME);
        $file = ROOT_PATH . '/runtime/' . $app . '/cache/' . $name . '.php';
        if ($dirMake) {
            dir_init(dirname($file), 0777);
        }
        return $file;
    }
}