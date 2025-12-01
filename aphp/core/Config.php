<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
/**
 * 配置处理类
 */
class Config
{
    use Single;

    protected static array $items = []; // 配置项
    protected string $cachePath; // 缓存路径
    protected array $fileList = []; // 配置文件列表

    private function __construct(string|array $load = [])
    {
        $this->cachePath = dir_init(RUNTIME_PATH . '/config', 0777);
        if (!empty($load)) {
            $this->load($load);
        }
    }

    // 加载配置
    public function load(string|array $load = []): void
    {
        if (!empty($load)) {
            $fileList = $this->parseFileList($load);
            $this->fileList = array_merge($this->fileList, $fileList);
        }
        $last_time = file_last_time($this->fileList);
        $cache_file = $this->cachePath . '/' . md5(json_encode($this->fileList) . $last_time) . '.php';
        if (file_exists($cache_file)) {
            $data = file_get_contents($cache_file);
            self::$items = json_validate($data) ? json_decode($data, true) : [];
        } else {
            dir_delete($this->cachePath);
            foreach ($this->fileList as $file) {
                if (file_exists($file)) {
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    if ($ext == 'php') {
                        $data = include $file;
                        if (is_array($data)) {
                            arr_key_case($data);
                            $name = basename($file, '.php');
                            self::$items[$name] = isset(self::$items[$name]) ? array_replace_recursive(self::$items[$name], $data) : $data;
                        }
                    } elseif ($ext == 'env') {
                        $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
                        if (is_array($data)) {
                            arr_key_case($data);
                            self::$items = array_replace_recursive(self::$items, $data);
                        }
                    }
                }
            }
            file_put_contents($cache_file, json_encode(self::$items));
        }
    }

    // 解析加载文件列表
    private function parseFileList(string|array $load = []): array
    {
        if (is_string($load)) {
            return dir_get_file($load, ['php', 'env']);
        }
        $list = array_map(fn($dir) => dir_get_file($dir, ['php', 'env']), $load);
        return array_reduce($list, 'array_merge', []);
    }

    // 获取配置项
    public function get(string $name = '', mixed $default = '', bool $to_array = false): mixed
    {
        if (empty($name)) {
            return self::$items;
        }
        return arr_get(self::$items, $name, $default, $to_array);
    }

    // 设置配置项
    public function set(string $name, mixed $value = ''): mixed
    {
        return arr_set(self::$items, $name, $value);
    }

    // 是否存在配置项
    public function has(string $name): bool
    {
        return arr_has(self::$items, $name);
    }
}