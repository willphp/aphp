<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
class Config
{
    use Single;

    protected static array $items = [];
    protected array $res;
    protected string $cacheDir;

    private function __construct(array $dirs = [])
    {
        $this->res = !empty($dirs) ? Tool::dir_file_list($dirs) : [];
        $this->cacheDir = Tool::dir_init(RUNTIME_PATH . '/config', 0777);
        $this->load();
    }

    public function load(string $file = ''): void
    {
        if (!empty($file) && is_file($file)) {
            $this->res[] = $file;
        }
        $lastTime = Tool::file_list_mtime($this->res);
        $cacheFile = $this->cacheDir . '/' . md5(serialize($this->res) . $lastTime) . '.php';
        if (file_exists($cacheFile)) {
            $data = file_get_contents($cacheFile);
            self::$items = json_validate($data) ? json_decode($data, true) : [];
        } else {
            $this->refresh();
            foreach ($this->res as $file) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if ($ext == 'php') {
                    $data = include $file;
                    if ($data) {
                        Tool::arr_key_case($data);
                        $name = basename($file, '.php');
                        self::$items[$name] = isset(self::$items[$name]) ? array_replace_recursive(self::$items[$name], $data) : $data;
                    }
                } elseif ($ext == 'env') {
                    $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
                    if ($data) {
                        Tool::arr_key_case($data);
                        self::$items = array_replace_recursive(self::$items, $data);
                    }
                }
            }
            file_put_contents($cacheFile, json_encode(self::$items));
        }
    }

    public function get(string $name = '', $default = '')
    {
        if (empty($name)) {
            return self::$items;
        }
        return Tool::arr_get(self::$items, $name, $default);
    }

    public function set(string $name, $value = '')
    {
        return Tool::arr_set(self::$items, $name, $value);
    }

    public function has(string $name): bool
    {
        return Tool::arr_has(self::$items, $name);
    }

    public function refresh(): bool
    {
        return Tool::dir_delete($this->cacheDir);
    }
}