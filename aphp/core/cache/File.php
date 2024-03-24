<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core\cache;

use aphp\core\Config;
use aphp\core\Tool;

class File extends Base
{
    public function connect()
    {
    }

    public function set(string $name, $value, int $expire = 0): bool
    {
        $file = $this->parseName($name, true);
        $content = sprintf("%010d", $expire) . json_encode($value);
        return (bool)file_put_contents($file, $content);
    }

    public function get(string $name, $default = null)
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
        return !is_file($file) or unlink($file);
    }

    public function has(string $name): bool
    {
        return (bool)$this->get($name);
    }

    public function flush(string $path = ''): bool
    {
        //clear all
        if ($path == '*') {
            $appList = Config::init()->get('app.app_list', []);
            $appList[] = 'common';
            foreach ($appList as $app) {
                Tool::dir_delete(APHP_TOP . '/runtime/' . $app . '/cache');
            }
            return true;
        }
        //clear current app
        if (empty($path)) {
            return Tool::dir_delete(APHP_TOP . '/runtime/' . APP_NAME . '/cache');
        }
        [$app, $path] = parse_app_name($path);
        if ($path == '*') {
            //clear app
            return Tool::dir_delete(APHP_TOP . '/runtime/' . $app . '/cache');
        }
        $path = rtrim($path, '*');
        //clear path
        return Tool::dir_delete(APHP_TOP . '/runtime/' . $app . '/cache/' . $path, true);
    }

    private function parseName(string $name, bool $dirMake = false): string
    {
        [$app, $name] = parse_app_name($name);
        $file = APHP_TOP . '/runtime/' . $app . '/cache/' . $name . '.php';
        if ($dirMake) {
            Tool::dir_init(dirname($file), 0777);
        }
        return $file;
    }
}