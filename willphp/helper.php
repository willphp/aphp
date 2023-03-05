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
/**
 * 支持php8新函数
 */
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle): bool
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle): bool
    {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle): bool
    {
        return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
    }
}
/**
 * 创建目录
 */
function dir_create(string $dir, int $auth = 0755): bool
{
    return !empty($dir) && (is_dir($dir) or mkdir($dir, $auth, true));
}

/**
 * 删除目录
 */
function dir_del(string $dir): bool
{
    if (!is_dir($dir)) return true;
    $list = array_diff(scandir($dir), ['.', '..']);
    foreach ($list as $file) {
        is_dir("$dir/$file") ? dir_del("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

/**
 * 数组键名大小写转换
 */
function array_key_case(array &$array, int $case = CASE_LOWER): void
{
    $array = array_change_key_case($array, $case);
    foreach ($array as $key => $value) {
        if (is_array($value)) array_key_case($array[$key], $case);
    }
}

/**
 * 数组值大小写转换
 */
function array_value_case(array &$array, int $case = CASE_LOWER): void
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            array_value_case($array[$key], $case);
            continue;
        }
        $array[$key] = ($case == CASE_LOWER) ? strtolower($value) : strtoupper($value);
    }
}

/**
 * 驼峰转下划线
 */
function name_snake(string $name): string
{
    return strtolower(trim(preg_replace('/([A-Z])/', '_\1\2', $name), '_'));
}

/**
 * 下划线转驼峰
 */
function name_camel(string $name): string
{
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
}

/**
 * 点语法获取数组值
 */
function array_dot_get(array $array, string $key, $default = '')
{
    $keyArr = explode('.', $key);
    $value = $array;
    foreach ($keyArr as $k) {
        $value = $value[$k] ?? $default;
    }
    return $value;
}

/**
 * 点语法设置数组值
 */
function array_dot_set(array &$array, string $key, $value = '')
{
    $temp = &$array;
    $keyArr = explode('.', $key);
    foreach ($keyArr as $k) {
        $temp[$k] ??= [];
        $temp = &$temp[$k];
    }
    return $temp = $value;
}

/**
 * 点语法检测键是否存在
 */
function array_dot_has(array $array, string $key): bool
{
    $temp = $array;
    $keyArr = explode('.', $key);
    foreach ($keyArr as $k) {
        if (!isset($temp[$k])) return false;
        $temp = $temp[$k];
    }
    return true;
}

/**
 * 获取批量函数
 */
function get_batch_func($batchFunc): array
{
    if (is_string($batchFunc)) {
        $batchFunc = explode(',', strtr($batchFunc, '|', ','));
    }
    return array_filter($batchFunc);
}

/**
 * 进行批量函数处理
 */
function value_batch_func($value, $batchFunc)
{
    $batchFunc = get_batch_func($batchFunc);
    foreach ($batchFunc as $func) {
        if (function_exists($func)) {
            if (is_scalar($value)) {
                $value = $func(strval($value));
            } elseif (is_array($value)) {
                $value = array_map(fn($v) => is_scalar($v) ? $func(strval($v)) : $v, $value);
            }
        }
    }
    return $value;
}

/**
 * 检测是否跳过
 */
function is_continue(int $at, array $data, string $field): bool
{
    if ($at == AT_NOT_NULL && empty($data[$field])) {
        return true;
    }
    if ($at == AT_NULL && !empty($data[$field])) {
        return true;
    }
    if ($at == AT_SET && !isset($data[$field])) {
        return true;
    }
    if ($at == AT_NOT_SET && isset($data[$field])) {
        return true;
    }
    return false;
}

/**
 * 拆分type.name
 */
function split_name(string $name, string $type, string $needle = '.'): array
{
    $name = trim($name, $needle);
    return str_contains($name, $needle) ? explode($needle, $name) : [$type, $name];
}

/**
 * 快速获取配置
 */
function get_config(string $name = '', $default = '')
{
    return \willphp\core\Config::init()->get($name, $default);
}

/**
 * 快速设置配置
 */
function set_config(string $name = '', $value = '')
{
    return \willphp\core\Config::init()->set($name, $value);
}

/**
 * 更新配置缓存
 */
function update_config()
{
    return \willphp\core\Config::init()->update();
}

/**
 * 配置获取和设置
 */
function config(string $name = '', $value = null, string $type = '')
{
    $config = \willphp\core\Config::init();
    if (empty($name)) {
        return empty($type) ? $config->all() : $config->get($type);
    }
    $type = !empty($type) ? $type . '.' : '';
    if ($name[0] === '?') {
        $name = substr($name, 1);
        return (null === $value) ? $config->has($type . $name) : $config->get($type . $name, $value);
    }
    return (null === $value) ? $config->get($type . $name) : $config->set($type . $name, $value);
}

/**
 * site配置获取和设置
 */
function site(string $name = '', $value = null)
{
    return config($name, $value, 'site');
}

/**
 * 获取缓存(不存在则写入)
 */
function get_cache(string $name, ?Closure $closure = null, int $expire = 0)
{
    return \willphp\core\Cache::driver()->getCache($name, $closure, $expire);
}

/**
 * 缓存获取和设置
 */
function cache(?string $name = '', $value = '', int $expire = 0)
{
    $cache = \willphp\core\Cache::driver();
    if ('' === $name) {
        return $cache;
    }
    if (null === $name) {
        return $cache->flush();
    }
    if ('' === $value) {
        return str_starts_with($name, '?') ? $cache->has(substr($name, 1)) : $cache->get($name);
    }
    if (null === $value) {
        return $cache->del($name);
    }
    return $cache->set($name, $value, $expire);
}

/**
 * 获取类单例
 */
function app(string $class): object
{
    return \willphp\core\App::make($class);
}

/**
 * 获取数据表对象
 */
function db(string $table = '', $config = []): object
{
    return \willphp\core\Db::connect($config, $table);
}

/**
 * 获取模型对象
 */
function model(string $name = ''): object
{
    [$module, $name] = split_name($name, APP_NAME);
    $class = '\\app\\' . $module . '\\model\\' . name_camel($name);
    return call_user_func([$class, 'init']);
}

/**
 * 获取部件对象
 */
function widget(string $name): object
{
    [$module, $name] = split_name($name, APP_NAME);
    $class = '\\app\\' . $module . '\\widget\\' . name_camel($name);
    return app($class);
}

/**
 * 获取验证器对象
 */
function validate(array $validate, array $data = [], bool $isBatch = false): object
{
    return \willphp\core\Validate::init()->make($validate, $data, $isBatch);
}

/**
 * url生成
 */
function url(string $route = '', array $params = [], string $suffix = '*'): string
{
    return \willphp\core\Route::init()->buildUrl($route, $params, $suffix);
}

/**
 * 获取输入请求
 */
function input(string $name, $default = null, $batchFunc = [])
{
    return \willphp\core\Request::init()->getRequest($name, $default, $batchFunc);
}

/**
 * 记录trace到调试栏
 */
function trace($info = '', string $level = 'debug'): void
{
    \willphp\core\Debug::init()->trace($info, $level);
}

/**
 * 获取视图对象
 */
function view(string $file = '', array $vars = []): object
{
    return \willphp\core\View::init()->make($file, $vars);
}

/**
 * 获取视图并传值
 */
function view_with($vars, $value = ''): object
{
    return \willphp\core\View::init()->setFile()->with($vars, $value);
}

/**
 * 生成表单令牌字段
 */
function csrf_field(string $name = 'csrf_token'): string
{
    return "<input type='hidden' name='$name' value='" . csrf_token($name) . "'/>\r\n";
}

/**
 * 获取表单令牌值
 */
function csrf_token(string $name = 'csrf_token'): string
{
    return session($name);
}

/**
 * 字符串加密
 */
function encrypt(string $str, string $key = ''): string
{
    return \willphp\core\Crypt::init()->encrypt($str, $key);
}

/**
 * 字符串解密
 */
function decrypt(string $str, string $key = ''): string
{
    return \willphp\core\Crypt::init()->decrypt($str, $key);
}

function halt(string $msg, int $code = 400, array $params = []): void
{
    \willphp\core\Response::halt($msg, $code, $params);
}

/**
 * Cookie设置和获取
 */
function cookie(?string $name = '', $value = '', int $expire = 0, string $path = null, string $domain = null)
{
    $cookie = \willphp\core\Cookie::init();
    if ('' === $name) {
        return $cookie->all();
    }
    if (null === $name) {
        return $cookie->flush();
    }
    if ('' === $value) {
        return str_starts_with($name, '?') ? $cookie->has(substr($name, 1)) : $cookie->get($name);
    }
    if (null === $value) {
        return $cookie->del($name);
    }
    return $cookie->set($name, $value, $expire, $path, $domain);
}

/**
 * Session设置和获取
 */
function session(?string $name = '', $value = '')
{
    $session = \willphp\core\Session::init();
    if ('' === $name) {
        return $session->all();
    }
    if (null === $name) {
        return $session->flush();
    }
    if ('' === $value) {
        return str_starts_with($name, '?') ? $session->has(substr($name, 1)) : $session->get($name);
    }
    if (null === $value) {
        return $session->del($name);
    }
    return $session->set($name, $value);
}

/**
 * 清理html代码
 */
function clear_html(string $string): string
{
    $string = strip_tags($string);
    $string = preg_replace(['/\t/', '/\r\n/', '/\r/', '/\n/'], '', $string);
    return trim($string);
}