<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;

use Exception;

class Tool
{
    // get array value (name: key.key)
    public static function arr_get(array $data, string $name, $default = '')
    {
        $keys = explode('.', $name);
        $val = $data;
        foreach ($keys as $key) {
            $val = $val[$key] ?? $default;
        }
        return $val;
    }

    // set array value (name: key.key)
    public static function arr_set(array &$data, string $name, $value = '')
    {
        $keys = explode('.', $name);
        $temp = &$data;
        foreach ($keys as $key) {
            $temp[$key] ??= [];
            $temp = &$temp[$key];
        }
        return $temp = $value;
    }

    // isset array (name: key.key)
    public static function arr_has(array $data, string $name): bool
    {
        $keys = explode('.', $name);
        $temp = $data;
        foreach ($keys as $key) {
            if (!isset($temp[$key])) {
                return false;
            }
            $temp = $temp[$key];
        }
        return true;
    }

    // array key name to CASE_LOWER or CASE_UPPER
    public static function arr_key_case(array &$data, int $case = CASE_LOWER): void
    {
        $data = array_change_key_case($data, $case);
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                self::arr_key_case($data[$key], $case);
            }
        }
    }

    // array value to CASE_LOWER or CASE_UPPER
    public static function arr_value_case(array &$data, int $case = CASE_LOWER): void
    {
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                self::arr_value_case($data[$key], $case);
                continue;
            }
            if (is_string($val)) {
                $data[$key] = ($case == CASE_LOWER) ? strtolower($val) : strtoupper($val);
            }
        }
    }

    // array key filter (in_true: reserve key,in_false: remove key)
    public static function arr_key_filter(array $data, array $filter, bool $in = false): array
    {
        $callback = fn($key) => $in ? in_array($key, $filter) : !in_array($key, $filter);
        return array_filter($data, $callback, ARRAY_FILTER_USE_KEY);
    }

    // create directory
    public static function dir_create(string $dir, int $auth = 0755): bool
    {
        return !empty($dir) && (is_dir($dir) or mkdir($dir, $auth, true));
    }

    // delete directory
    public static function dir_delete(string $dir, bool $delRoot = false): bool
    {
        if (!is_dir($dir)) return true;
        $file_list = array_diff(scandir($dir), ['.', '..']);
        foreach ($file_list as $file) {
            is_dir("$dir/$file") ? self::dir_delete("$dir/$file", true) : unlink("$dir/$file");
        }
        return !$delRoot || rmdir($dir);
    }

    // initialization directory
    public static function dir_init(string $dir, int $auth = 0755): string
    {
        if (!self::dir_create($dir, $auth)) {
            throw new Exception('Failed to create ' . substr($dir, strlen(APHP_TOP . '/')) . ' directory.');
        }
        return $dir;
    }

    // get directory file list
    public static function dir_file_list(array $dirs, string $ext = '.php'): array
    {
        $list = [];
        foreach ($dirs as $dir) {
            if (is_file($dir)) {
                $list[] = $dir;
            } elseif (is_dir($dir)) {
                $list = array_merge($list, glob($dir . '/*' . $ext));
            }
        }
        return $list;
    }

    // get file list modify time
    public static function file_list_mtime(array $list): int
    {
        if (!empty($list)) {
            $mtime = array_map(fn(string $file): int => filemtime($file), $list);
            return max($mtime);
        }
        return 0;
    }

    // size to kb
    public static function size2kb(int $size): string
    {
        $unitByte = [' TB' => 1099511627776, ' GB' => 1073741824, ' MB' => 1048576];
        foreach ($unitByte as $unit => $byte) {
            if ($size >= $byte) {
                return round($size / $byte, 2) . $unit;
            }
        }
        return number_format($size / 1024, 2) . ' KB';
    }
}