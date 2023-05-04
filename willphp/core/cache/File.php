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

use willphp\core\Config;
use willphp\core\Dir;

class File extends Base
{
    public function connect()
    {
    }

    public function set(string $name, $data, int $expire = 0): bool
    {
        $file = $this->getFile($name, true);
        $content = sprintf("%010d", $expire) . json_encode($data);
        return (bool)file_put_contents($file, $content);
    }

    public function get(string $name, $default = null)
    {
        $file = $this->getFile($name);
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
        $file = $this->getFile($name);
        return !is_file($file) or unlink($file);
    }

    public function has(string $name): bool
    {
        return (bool)$this->get($name);
    }

    public function flush(string $prefix = '[app]'): bool
    {
        if ($prefix == '[all]') {
            $appList = Config::init()->get('app.app_list', []);
            $appList[] = 'common';
            foreach ($appList as $app) {
                Dir::del(ROOT_PATH . '/runtime/' . $app . '/cache');
            }
            return true;
        }
        if ($prefix == '[app]' || empty($prefix)) {
            return Dir::del(ROOT_PATH . '/runtime/' . APP_NAME . '/cache');
        }
        [$app, $prefix] = pre_split($prefix, APP_NAME, '@');
        $dir = rtrim(ROOT_PATH . '/runtime/' . $app . '/cache/' . $prefix, '*');
        return Dir::del($dir, true);
    }

    private function getFile(string $name, bool $dirMake = false): string
    {
        [$app, $path] = pre_split($name, APP_NAME, '@');
        $file = ROOT_PATH . '/runtime/' . $app . '/cache/' . $path . '.php';
        if ($dirMake) {
            Dir::make(dirname($file), 0777);
        }
        return $file;
    }
}