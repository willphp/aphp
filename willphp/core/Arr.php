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
 * 数组操作增强类
 */
class Arr
{
    //以键名.键名方式获取数组值
    public static function get(array $data, string $name, $default = '')
    {
        $keys = explode('.', $name);
        $val = $data;
        foreach ($keys as $key) {
            $val = $val[$key] ?? $default;
        }
        return $val;
    }

    //以键名.键名方式设置数组值
    public static function set(array &$data, string $name, $value = '')
    {
        $keys = explode('.', $name);
        $temp = &$data;
        foreach ($keys as $key) {
            $temp[$key] ??= [];
            $temp = &$temp[$key];
        }
        return $temp = $value;
    }

    //以键名.键名方式判断键名是否存在
    public static function has(array $data, string $name): bool
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

    //数组键名大小写转换
    public static function keyCase(array &$data, int $case = CASE_LOWER): void
    {
        $data = array_change_key_case($data, $case);
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                self::keyCase($data[$key], $case);
            }
        }
    }

    //数组值大小写转换
    public static function valueCase(array &$data, int $case = CASE_LOWER): void
    {
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                self::valueCase($data[$key], $case);
                continue;
            }
            $data[$key] = ($case == CASE_LOWER) ? strtolower($val) : strtoupper($val);
        }
    }

    //根键名数组对数组进行过滤
    public static function keyFilter(array $data, array $filter, bool $in = false): array
    {
        $callback = fn($key) => $in ? in_array($key, $filter) : !in_array($key, $filter);
        return array_filter($data, $callback, ARRAY_FILTER_USE_KEY);
    }
}