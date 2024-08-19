<?php
/*------------------------------------------------------------------
 | 配置类 2024-08-15 by 无念
 |------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);
namespace aphp\core;
class Config
{
    use Single;
    protected static array $items = []; // 配置项
    protected string $cachePath; // 缓存路径
    protected array $fileList = []; // 配置文件列表

    private function __construct(array $load = [])
    {
        $this->cachePath = Tool::dir_init(RUNTIME_PATH . '/config', 0777);
        if (!empty($load)) {
            $this->fileList = Tool::dir_file_list($load);
        }
        $this->load();
    }

    // 加载配置项
    public function load(array $load = []): void
    {
        if (!empty($load)) {
            $load = Tool::dir_file_list($load);
            $this->fileList = array_merge($this->fileList, $load);
        }
        $lastTime = Tool::file_list_mtime($this->fileList);
        $cacheFile = $this->cachePath . '/' . md5(json_encode($this->fileList) . $lastTime) . '.php';
        if (file_exists($cacheFile)) {
            $data = file_get_contents($cacheFile);
            self::$items = json_validate($data) ? json_decode($data, true) : [];
        } else {
            $this->reload();
            foreach ($this->fileList as $file) {
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

    // 重载配置项
    public function reload(): bool
    {
        return Tool::dir_delete($this->cachePath);
    }

    // 获取配置项
    public function get(string $name = '', $default = '', bool $to_array = false)
    {
        if (empty($name)) {
            return self::$items;
        }
        return Tool::arr_get(self::$items, $name, $default, $to_array);
    }

    // 设置配置项
    public function set(string $name, $value = '')
    {
        return Tool::arr_set(self::$items, $name, $value);
    }

    // 配置项是否存在
    public function has(string $name): bool
    {
        return Tool::arr_has(self::$items, $name);
    }
}