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
class Config
{
    use Single;

    protected static array $items = [];
    protected string $cacheDir;

    private function __construct()
    {
        $this->cacheDir = RUNTIME_PATH.'/config';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $configPath = [ROOT_PATH . '/config', APP_PATH . '/config', ROOT_PATH . '/.env'];
        $configMtime = get_mtime_batch($configPath);
        $cacheFile = $this->cacheDir. '/'.$configMtime.'.php';
        if (!file_exists($cacheFile)) {
            $this->update();
            foreach ($configPath as $res) {
                $this->load($res);
            }
            file_put_contents($cacheFile, json_encode(self::$items));
        } else {
            $data = file_get_contents($cacheFile);
            self::$items = json_decode($data, true);
        }
    }

    public function update(): bool
    {
        return dir_del($this->cacheDir);
    }

    public function all(): array
    {
        return self::$items;
    }

    public function reset(array $config = []): array
    {
        return self::$items = $config;
    }

    public function get(string $key = '', $default = '')
    {
        return empty($key) ? self::$items : array_dot_get(self::$items, $key, $default);
    }

    public function set(string $key, $value = '')
    {
        return array_dot_set(self::$items, $key, $value);
    }

    public function has(string $key): bool
    {
        return array_dot_has(self::$items, $key);
    }

    public function load(string $res): void
    {
        if (is_dir($res)) {
            $list = glob($res . '/*.php');
            foreach ($list as $file) {
                $this->loadFile($file);
            }
        } elseif (is_file($res)) {
            $this->loadFile($res);
        }
    }

    public function loadFile(string $file): void
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $data = [];
        $name = '';
        if ($ext == 'php') {
            $name = strtolower(basename($file, '.php'));
            $data = include $file;
        } elseif ($ext == 'env') {
            $data = parse_ini_file($file, true, INI_SCANNER_TYPED) ?: [];
        }
        if ($data) {
            array_key_case($data);
            if ($name) {
                self::$items[$name] = isset(self::$items[$name]) ? array_replace_recursive(self::$items[$name], $data) : $data;
            } else {
                self::$items = array_replace_recursive(self::$items, $data);
            }
        }
    }
}