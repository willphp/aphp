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

/**
 * 文件缓存类
 */
class File extends Base
{
    public function connect()
    {
    }

    //设置
    public function set(string $name, $data, int $expire = 0): bool
    {
        $file = $this->parseName($name, true);
        $content = sprintf("%010d", $expire) . json_encode($data);
        return (bool)file_put_contents($file, $content);
    }

    //获取
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

    //删除
    public function del(string $name): bool
    {
        $file = $this->parseName($name);
        return !is_file($file) or unlink($file);
    }

    //检测存在
    public function has(string $name): bool
    {
        return (bool)$this->get($name);
    }

    //清空
    public function flush(string $type = '[app]'): bool
    {
        //清空当前应用
        if ($type == '[app]' || empty($type)) {
            return Dir::del(ROOT_PATH . '/runtime/' . APP_NAME . '/cache');
        }
        //清空所有应用
        if ($type == '[all]') {
            $appList = Config::init()->get('app.app_list', []);
            $appList[] = 'common';
            foreach ($appList as $app) {
                Dir::del(ROOT_PATH . '/runtime/' . $app . '/cache');
            }
            return true;
        }
        //清空指定应用目录 应用@目录
        $type = trim($type, '@');
        $app = APP_NAME;
        if (str_contains($type, '@')) {
            [$app, $type] = explode('@', $type, 2);
        }
        $dir = rtrim(ROOT_PATH . '/runtime/' . $app . '/cache/' . $type, '*');
        return Dir::del($dir, true);
    }

    //解析文件名
    private function parseName(string $name, bool $dirMake = false): string
    {
        $name = trim($name, '@');
        $app = APP_NAME;
        if (str_contains($name, '@')) {
            [$app, $name] = explode('@', $name, 2);
        }
        $file = ROOT_PATH . '/runtime/' . $app . '/cache/' . $name . '.php';
        if ($dirMake) {
            Dir::make(dirname($file), 0777);
        }
        return $file;
    }
}