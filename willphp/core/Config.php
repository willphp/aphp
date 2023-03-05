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
    protected string $cacheFile;

    private function __construct()
    {
        if (!is_dir(RUNTIME_PATH)) {
            mkdir(RUNTIME_PATH, 0777, true);
        }
        $this->cacheFile = RUNTIME_PATH . '/config.php';
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        if (!file_exists($this->cacheFile)) {
            $config = [ROOT_PATH . '/config', APP_PATH . '/config', ROOT_PATH . '/.env'];
            foreach ($config as $res) {
                $this->load($res);
            }
            file_put_contents($this->cacheFile, json_encode(self::$items));
        } else {
            $data = file_get_contents($this->cacheFile);
            self::$items = json_decode($data, true);
        }
    }

    public function update(): bool
    {
        return !file_exists($this->cacheFile) or unlink($this->cacheFile);
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
        $key = '';
        if ($ext == 'php') {
            $key = strtolower(basename($file, '.php'));
            $data = include $file;
        } elseif ($ext == 'env') {
            $data = parse_ini_file($file, true, INI_SCANNER_TYPED) ?: [];
        }
        if ($data) {
            array_key_case($data);
            if ($key) {
                self::$items[$key] = isset(self::$items[$key]) ? array_replace_recursive(self::$items[$key], $data) : $data;
            } else {
                self::$items = array_replace_recursive(self::$items, $data);
            }
        }
    }
}