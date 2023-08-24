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
/**
 * 配置处理类
 */
class Config
{
    use Single;

    protected static array $items = [];

    //初始化配置
    private function __construct(array $dirs = [])
    {
        if (!empty($dirs)) {
            $files = Dir::getFiles($dirs);
            $mtime = Dir::getMtime($files);
            $cacheDir = Dir::make(RUNTIME_PATH . '/config', 0777);
            $cacheFile = $cacheDir . '/' . $mtime . '.php';
            if (!file_exists($cacheFile)) {
                Dir::del($cacheDir);
                foreach ($files as $file) {
                    $this->parseFile($file);
                }
                file_put_contents($cacheFile, json_encode(self::$items));
            } else {
                $data = file_get_contents($cacheFile);
                self::$items = json_decode($data, true);
            }
        }
    }

    //配置文件处理
    public function parseFile(string $file): void
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if ($ext == 'php') {
            $data = include $file;
            if ($data) {
                Arr::keyCase($data);
                $name = basename($file, '.php');
                self::$items[$name] = isset(self::$items[$name]) ? array_replace_recursive(self::$items[$name], $data) : $data;
            }
        }
        if ($ext == 'env') {
            $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
            if ($data) {
                Arr::keyCase($data);
                self::$items = array_replace_recursive(self::$items, $data);
            }
        }
    }

    //获取全部
    public function all(): array
    {
        return self::$items;
    }

    //重置全部
    public function reset(array $config = []): array
    {
        return self::$items = $config;
    }

    //获取
    public function get(string $name = '', $default = '')
    {
        if (empty($name)) {
            return self::$items;
        }
        return Arr::get(self::$items, $name, $default);
    }

    //设置
    public function set(string $name, $value = '')
    {
        return Arr::set(self::$items, $name, $value);
    }

    //检测存在
    public function has(string $name): bool
    {
        return Arr::has(self::$items, $name);
    }
}