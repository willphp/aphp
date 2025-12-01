<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);
/**
 * 框架核心函数
 */

// 验证是否为json字符串
if (!function_exists('json_validate')) {
    function json_validate(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

// 调试输出
function dump(...$vars): void
{
    ob_start();
    var_dump(...$vars);
    $output = ob_get_clean();
    $output = preg_replace('/]=>\n(\s+)/m', '] => ', $output);
    if (PHP_SAPI == 'cli') {
        $output = PHP_EOL . $output . PHP_EOL;
    } elseif (!extension_loaded('xdebug')) {
        $output = '<pre>' . htmlspecialchars($output, ENT_SUBSTITUTE) . '</pre>';
    }
    echo $output;
}

// 调试输出并结束
function dd(...$vars): never
{
    dump(...$vars);
    exit();
}

// 输出用户常量
function dump_const(): void
{
    $const = get_defined_constants(true);
    dump($const['user'] ?? []);
}

// 以key.key的方式设置数组值, 返回设置的值
function arr_set(array &$array, string $name, mixed $value = ''): mixed
{
    if (str_contains($name, '.')) {
        $keys = explode('.', $name);
        $temp = &$array;
        foreach ($keys as $key) {
            $temp[$key] ??= [];
            $temp = &$temp[$key];
        }
        return $temp = $value;
    }
    return $array[$name] = $value;
}

// 以key.key的方式获取数组值
function arr_get(array $array, string $name, mixed $default = '', bool $to_array = false): mixed
{
    if (str_contains($name, '.')) {
        $keys = explode('.', $name);
        $value = $array;
        foreach ($keys as $key) {
            $value = $value[$key] ?? $default;
        }
    } else {
        $value = $array[$name] ?? $default;
    }
    return $to_array && !is_array($value) ? str_to_array($value) : $value;
}

// 以key.key的方式检测数组键名是否存在
function arr_has(array $array, string $name): bool
{
    if (!str_contains($name, '.')) {
        return isset($array[$name]);
    }
    $keys = explode('.', $name);
    $temp = $array;
    foreach ($keys as $key) {
        if (!isset($temp[$key])) {
            return false;
        }
        $temp = $temp[$key];
    }
    return true;
}

// 数组键名转换大小写 CASE_LOWER|CASE_UPPER
function arr_key_case(array &$array, int $case = CASE_LOWER): void
{
    $array = array_change_key_case($array, $case);
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            arr_key_case($array[$k], $case);
        }
    }
}

// 数组值转换大小写 CASE_LOWER|CASE_UPPER
function arr_value_case(array &$array, int $case = CASE_LOWER): void
{
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            arr_value_case($array[$k], $case);
            continue;
        }
        if (is_string($v)) {
            $array[$k] = ($case == CASE_LOWER) ? strtolower($v) : strtoupper($v);
        }
    }
}

// 根据键名过滤数组
function arr_key_filter(array $array, array $filter, bool $in_array = false): array
{
    $callback = fn($key) => $in_array ? in_array($key, $filter) : !in_array($key, $filter);
    return array_filter($array, $callback, ARRAY_FILTER_USE_KEY);
}

// 字符串分割转换为数组
function str_to_array(string $str, string $sep = '|', string $eq = '=', array $replace = ['[or]' => '|', '[eq]' => '=']): array
{
    if (empty($str)) {
        return [];
    }
    if (str_contains($str, "\n")) {
        $sep = "\n"; // 当有换行时使用换行分割
    }
    $array = array_diff(explode($sep, $str), ['']); // 删除空值
    $is_key = str_contains($str, $eq); // 是否有键名
    $is_replace = str_contains($str, '['); // 是否有分割符替换
    if (!$is_key) {
        return $is_replace ? array_map(fn($v) => strtr($v, $replace), $array) : $array;
    }
    $arr = [];
    $i = 0;
    foreach ($array as $k => $v) {
        if (str_contains($v, $eq)) {
            [$i, $v] = explode($eq, $v, 2);
        } elseif ($k != 0) {
            $i++;
        }
        if (is_string($i)) $i = trim($i);
        if (isset($arr[$i])) $i++;
        $arr[$i] = trim($v);
    }
    return $is_replace ? array_map(fn($v) => strtr($v, $replace), $arr) : $arr;
}

// 目录创建
function dir_create(string $dir, int $mode = 0755): bool
{
    return !empty($dir) && (is_dir($dir) or mkdir($dir, $mode, true));
}

// 目录初始化，失败抛出异常
function dir_init(string $dir, int $mode = 0755): string
{
    if (!dir_create($dir, $mode)) {
        throw new Exception('Failed to create ' . substr($dir, strlen(ROOT_PATH . '/')) . ' directory.');
    }
    return $dir;
}

// 目录删除
function dir_delete(string $dir, bool $del_root = false): bool
{
    if (!is_dir($dir)) return true;
    $list = array_diff(scandir($dir), ['.', '..']);
    foreach ($list as $file) {
        is_dir("$dir/$file") ? dir_delete("$dir/$file", true) : unlink("$dir/$file");
    }
    return !$del_root || rmdir($dir);
}

// 目录复制
function dir_copy(string $src, string $dst): bool
{
    if (!is_dir($src) || !dir_create($dst)) {
        return false;
    }
    $list = glob($src . '/*');
    foreach ($list as $file) {
        $dst_file = $dst . '/' . basename($file);
        is_file($file) ? copy($file, $dst_file) : dir_copy($file, $dst_file);
    }
    return true;
}

// 目录移动
function dir_move(string $src, string $dst): bool
{
    if (dir_copy($src, $dst)) {
        dir_delete($src, true);
        return true;
    }
    return false;
}

// 获取目录下所有指定后缀的文件列表
function dir_get_file(string $dir, array $ext = [], bool $recursive = true): array
{
    if (is_file($dir) || !is_dir($dir)) {
        if (empty($ext) || in_array(pathinfo($dir, PATHINFO_EXTENSION), $ext)) {
            return [$dir];
        }
        return [];
    }
    $list = [];
    $dirs = glob($dir . '/*');
    foreach ($dirs as $file) {
        if (is_dir($file)) {
            if ($recursive) {
                $list = array_merge($list, dir_get_file($file, $ext, $recursive));
            }
            continue;
        }
        if (empty($ext) || in_array(pathinfo($file, PATHINFO_EXTENSION), $ext)) {
            $list[] = $file;
        }
    }
    return $list;
}

// 获取文件列表最后更新时间
function file_last_time(string|array $file): int
{
    if (is_array($file) && !empty($file)) {
        $mtime = array_map(fn(string $v): int => is_file($v) ? filemtime($v) : 0, $file);
        return max($mtime);
    }
    return is_string($file) && is_file($file) ? filemtime($file) : 0;
}

// 文件复制
function file_copy(string $file, string $dst): bool
{
    if (!file_exists($file) || !dir_create($dst)) {
        return false;
    }
    return copy($file, $dst . '/' . basename($file));
}

// 文件移动
function file_move(string $file, string $dst): bool
{
    if (file_copy($file, $dst)) {
        unlink($file);
        return true;
    }
    return false;
}

// 文件删除
function file_delete(string $file): bool
{
    return !file_exists($file) || unlink($file);
}

// 大小转换成kb
function size_to_kb(int $size): string
{
    $unit_byte = [' TB' => 1099511627776, ' GB' => 1073741824, ' MB' => 1048576];
    foreach ($unit_byte as $unit => $byte) {
        if ($size >= $byte) {
            return round($size / $byte, 2) . $unit;
        }
    }
    return number_format($size / 1024, 2) . ' KB';
}

// 驼峰转下划线
function name_to_snake(string $name): string
{
    $result = strtolower(preg_replace('/([A-Z])/', '_$1', $name));
    return ltrim($result, '_');
}

// 下划线转驼峰
function name_to_camel(string $name): string
{
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
}

// 名称解析 应用.名称 分隔符默认：. 可设置多个：@:
function name_parse(string $name, string $app = '', string $sep = '@'): array
{
    if (empty($app)) {
        $app = defined('APP_NAME') ? APP_NAME : 'index';
    }
    $search = strlen($sep) > 1 ? str_split($sep) : $sep;
    $name = str_replace($search, '.', trim($name, $sep)); // 替换分隔符
    return str_contains($name, '.') ? explode('.', $name, 2) : [$app, $name];
}

// 解析批量函数
function parse_batch_func(array|string $func = []): array
{
    if (is_string($func)) {
        $func = explode(',', str_replace('|', ',', $func));
    }
    return array_filter($func);
}

// 对值执行批量函数
function exec_batch_func(mixed $value, array|string $func): mixed
{
    $func = parse_batch_func($func);
    foreach ($func as $fn) {
        if (function_exists($fn)) {
            $value = is_array($value) ? array_map(fn($v) => $fn(strval($v)), $value) : $fn(strval($value));
        }
    }
    return $value;
}

// 检查字段验证条件是否跳过
function field_validate_skip(int $fv, array $data, string $field): bool
{
    if ($fv === FV_VALUE) {
        return empty($data[$field]);
    }
    if ($fv === FV_EMPTY) {
        return !empty($data[$field]);
    }
    if ($fv === FV_ISSET) {
        return !isset($data[$field]);
    }
    if ($fv === FV_UNSET) {
        return isset($data[$field]);
    }
    return false;
}

// 清除html代码和空格换行
function clear_html(string $str): string
{
    return trim(preg_replace(['/\t/', '/\r\n/', '/\r/', '/\n/'], '', strip_tags($str)));
}

// 清除xss
function remove_xss(string $str): string
{
    $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $str);
    $tags_to_remove = ['javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base'];
    foreach ($tags_to_remove as $tag) {
        $str = preg_replace(['/<' . $tag . '\b[^>]*>/i', '/<\/' . $tag . '>/i'], '', $str);
    }
    $str = preg_replace_callback('/(<[^>]+)/', function ($match) {
        return preg_replace(['/javascript:/i', '/on[a-z]+=\s*"[^"]*"/i', '/on[a-z]+=\s*\'[^\']*\'/i', '/on[a-z]+\s*=\s*[^>]*/i'], '', $match[0]);
    }, $str);
    return preg_replace(['/data:\s*[^>]*/i', '/vbscript:\s*[^>]*/i', '/expression\s*\([^>]*\)/i'], '', $str);
}

// 字符串截取
function str_substr(string $str, int $length, int $start = 0, bool $suffix = true, string $charset = 'utf-8'): string
{
    $str = clear_html($str);
    if (function_exists("mb_substr")) {
        $slice = mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join('', array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice . '...' : $slice;
}

// ids过滤转换
function ids_filter(string $ids, bool $to_array = false, bool $gt_0 = true): array|string
{
    $ids = array_filter(explode(',', $ids), 'is_numeric');
    $ids = array_unique($ids);
    if ($gt_0) {
        $ids = array_filter($ids, fn(int $n) => $n > 0);
    }
    ksort($ids);
    return $to_array ? $ids : implode(',', $ids);
}

// curl请求
function get_curl(string $url, array $post = [], array $header = [], bool $get_header = false, string $cookie = '', string $referer = '', string $ua = '', bool $nobody = false, int $timeout = 30): string
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    if (!empty($post)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $http = ['Accept: */*', 'Accept-Encoding: gzip,deflate,sdch', 'Accept-Language: zh-CN,zh;q=0.8', 'Connection: close', 'X-API-Client: curl'];
    $header = array_merge($http, $header);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    if ($get_header) {
        curl_setopt($ch, CURLOPT_HEADER, true);
    }
    if (!empty($cookie)) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    if (!empty($referer)) {
        if ($referer == '1') {
            $referer = 'https://i.qq.com/';
        }
        curl_setopt($ch, CURLOPT_REFERER, $referer);
    }
    if (!empty($ua)) {
        $ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36';
    }
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    if ($nobody) {
        curl_setopt($ch, CURLOPT_NOBODY, true);
    }
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $ret = curl_exec($ch);
    curl_close($ch);
    return $ret ? trim($ret) : '';
}

// 获取ip
function get_ip(): string
{
    static $ip = null;
    if (null !== $ip) return $ip;
    $ip = '';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos) unset($arr[$pos]);
        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $long = ip2long((string)$ip);
    return $long ? $ip : '0.0.0.0';
}

// 获取整型ip
function get_ip_int(): int
{
    return ip2long(get_ip());
}

// 获取前后关联id
function get_related_id(int $id, array $keys): array
{
    $k = array_search($id, $keys);
    if ($k === false) {
        return [0, 0];
    }
    $prev_id = $keys[$k - 1] ?? 0;
    $next_id = $keys[$k + 1] ?? 0;
    return [$prev_id, $next_id];
}

// 获取上下篇
function get_prev_next(int $id, array $data): array
{
    [$prev_id, $next_id] = get_related_id($id, array_keys($data));
    $prev = $prev_id ? ['id' => $prev_id, 'title' => $data[$prev_id]] : [];
    $next = $next_id ? ['id' => $next_id, 'title' => $data[$next_id]] : [];
    return ['prev' => $prev, 'next' => $next];
}