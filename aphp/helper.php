<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

use aphp\core\App;
use aphp\core\Cache;
use aphp\core\Cli;
use aphp\core\Config;
use aphp\core\Cookie;
use aphp\core\Crypt;
use aphp\core\Db;
use aphp\core\db\Connection;
use aphp\core\Filter;
use aphp\core\Request;
use aphp\core\Route;
use aphp\core\Session;
use aphp\core\Validate;
use aphp\core\View;

if (!function_exists('str_contains')) {
    /**
     * 字符串包含
     */
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    /**
     * 字符串开始包含
     */
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    /**
     * 字符串结束包含
     */
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle !== '' && substr($haystack, -strlen($needle)) === $needle;
    }
}
if (!function_exists('json_validate')) {
    /**
     * 验证字符串Json
     */
    function json_validate(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

/**
 * 调试输出
 */
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

/**
 * 输出并结束
 */
function dd(...$vars): void
{
    dump(...$vars);
    exit();
}

/**
 * 输出用户常量
 */
function dump_const(): void
{
    dump(get_defined_constants(true)['user']);
}

/**
 * 解析 应用@名称
 */
function parse_app_name(string $name, string $app = ''): array
{
    if (empty($app)) {
        $app = APP_NAME;
    }
    $name = trim(strtolower($name), '@.');
    if (str_contains($name, '@')) {
        [$app, $name] = explode('@', $name, 2);
    } elseif (str_contains($name, '.')) {
        [$app, $name] = explode('.', $name, 2);
    }
    return [$app, $name];
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
 * 解析 前缀.名称
 */
function parse_prefix_name(string $name, string $default_prefix, string $needle = '.'): array
{
    $name = trim($name, $needle);
    return str_contains($name, $needle) ? explode($needle, $name, 2) : [$default_prefix, $name];
}

/**
 * 获取批量函数
 */
function get_batch_func($batchFunc = []): array
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
 * 检测条件跳过
 */
function check_at_continue(int $at, array $data, string $field): bool
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
 * 字符串加密
 */
function encrypt(string $string, string $salt = ''): string
{
    return Crypt::init()->encrypt($string, $salt);
}

/**
 * 字符串解密
 */
function decrypt(string $string, string $salt = ''): string
{
    return Crypt::init()->decrypt($string, $salt);
}

/**
 * 获取类单例
 */
function app(string $class): object
{
    return App::make($class);
}

/**
 * 命令调用
 */
function cli(string $uri, string $app = '', bool $isCall = true)
{
    if (empty($app)) {
        $app = APP_NAME;
    }
    return Cli::run($uri, $app, $isCall);
}

/**
 * 配置管理：设置，检测，获取，获取全部
 */
function config(string $name = '', $value = '')
{
    $config = Config::init();
    if ('' === $name) {
        return $config->all();
    }
    if ('' === $value) {
        return str_starts_with($name, '?') ? $config->has(substr($name, 1)) : $config->get($name);
    }
    return $config->set($name, $value);
}

/**
 * 获取配置
 */
function config_get(string $name = '', $default = '')
{
    return Config::init()->get($name, $default);
}

/**
 * 加载配置文件
 */
function config_load(string $file = ''): void
{
    Config::init()->load($file);
}

/**
 * 刷新配置
 */
function config_refresh(): bool
{
    return Config::init()->refresh();
}

/**
 * site配置获取
 */
function site(string $name = '', $default = '')
{
    $config = Config::init();
    if ('' === $name) {
        return $config->get('site');
    }
    return str_starts_with($name, '?') ? $config->has('site.' . substr($name, 1)) : $config->get('site.' . $name, $default);
}

/**
 * 缓存管理：设置，检测，获取，删除
 */
function cache(string $name, $value = '', int $expire = 0)
{
    $cache = Cache::init();
    if (null === $value) {
        return $cache->del($name);
    }
    if ('' === $value) {
        return str_starts_with($name, '?') ? $cache->has(substr($name, 1)) : $cache->get($name);
    }
    return $cache->set($name, $value, $expire);
}

/**
 * 缓存获取：不存在则制作
 */
function cache_make(string $name, ?Closure $closure = null, int $expire = 0)
{
    return Cache::init()->make($name, $closure, $expire);
}

/**
 * 清除缓存：指定路径
 */
function cache_clear(string $path = ''): bool
{
    return Cache::init()->flush($path);
}

/**
 * 获取部件(缓存)对象
 */
function widget(string $name): object
{
    [$app, $name] = parse_app_name($name);
    $class = 'app\\' . $app . '\\widget\\' . name_camel($name);
    return App::make($class);
}

/**
 * cookie管理：设置，检测，获取，删除，获取全部，清空
 */
function cookie(string $name = '', $value = '', array $options = [])
{
    $cookie = Cookie::init();
    if ('' === $name) {
        return $cookie->all();
    }
    if (null === $name) {
        return $cookie->flush();
    }
    if (null === $value) {
        return $cookie->del($name);
    }
    if ('' === $value) {
        return str_starts_with($name, '?') ? $cookie->has(substr($name, 1)) : $cookie->get($name);
    }
    return $cookie->set($name, $value, $options);
}

/**
 * session管理：设置，检测，获取，删除，获取全部，清空
 */
function session(?string $name = '', $value = '')
{
    $session = Session::init();
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
 * session闪存：用于Ajax设置临时数据，如验证码
 */
function session_flash(string $name = '', $value = '')
{
    return Session::init()->flash($name, $value);
}

/**
 * 过滤数据
 */
function filter_data(array $data): array
{
    Filter::init()->input($data);
    return $data;
}

/**
 * 获取输入请求
 */
function input(string $name, $default = null, $batchFunc = [])
{
    return Request::init()->getRequest($name, $default, $batchFunc);
}

/**
 * url生成
 */
function url(string $route = '', array $params = [], string $suffix = '*'): string
{
    return Route::init()->buildUrl($route, $params, $suffix);
}

/**
 * 获取当前控制器
 */
function get_controller(): string
{
    return Route::init()->getController();
}

/**
 * 获取当前方法
 */
function get_action(): string
{
    return Route::init()->getAction();
}

/**
 * 调用控制器方法
 */
function action(string $uri, array $params = [], string $app = '')
{
    return Route::init()->dispatch($uri, $params, $app);
}

/**
 * 获取视图对象
 */
function view(string $file = '', array $vars = [], bool $isCall = false): object
{
    return View::init()->make($file, $vars, $isCall);
}

/**
 * 数据库快速操作
 */
function pdo($config = []): object
{
    return Connection::init($config);
}

/**
 * 获取数据表对象
 */
function db(string $table = '', $config = []): object
{
    return Db::connect($config, $table);
}

/**
 * 获取模型对象
 */
function model(string $name = ''): object
{
    [$app, $name] = parse_app_name($name);
    $class = 'app\\' . $app . '\\model\\' . name_camel($name);
    return App::make($class);
}

/**
 * 获取验证器对象
 */
function validate(array $validate, array $data = [], bool $isBatch = false): object
{
    return Validate::init()->make($validate, $data, $isBatch);
}

/**
 * 字符串截取
 */
function str_substr($str, $length, $start = 0, $suffix = true, $charset = 'utf-8'): string
{
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

/**
 * 清理html代码
 */
function clear_html(string $string): string
{
    $string = strip_tags($string);
    $string = preg_replace(['/\t/', '/\r\n/', '/\r/', '/\n/'], '', $string);
    return trim($string);
}

/**
 * 清除xss脚本
 */
function remove_xss(string $val): string
{
    $val = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $val);
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val);
        $val = preg_replace('/(�{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val);
    }
    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);
    $found = true;
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(�{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2);
            $val = preg_replace($pattern, $replacement, $val);
            if ($val_before == $val) {
                $found = false;
            }
        }
    }
    return $val;
}