<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
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
use aphp\core\DebugBar;
use aphp\core\Filter;
use aphp\core\Log;
use aphp\core\Request;
use aphp\core\Response;
use aphp\core\Route;
use aphp\core\Session;
use aphp\core\Tool;
use aphp\core\Validate;
use aphp\core\View;

// =======================PHP8兼容函数===========================
if (!function_exists('str_contains')) {
    // 字符串包含
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    // 字符串开始包含
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    // 字符串结束包含
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle !== '' && substr($haystack, -strlen($needle)) === $needle;
    }
}
if (!function_exists('json_validate')) {
    // 验证字符串是否为Json
    function json_validate(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

// =======================调试输出函数===========================
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

// 输出并结束
function dd(...$vars): void
{
    dump(...$vars);
    exit();
}

// 输出用户常量
function dump_const(): void
{
    dump(get_defined_constants(true)['user']);
}

// =======================框架必要函数===========================
// 驼峰转下划线
function name_to_snake(string $name): string
{
    return strtolower(trim(preg_replace('/([A-Z])/', '_\1\2', $name), '_'));
}

// 下划线转驼峰
function name_to_camel(string $name): string
{
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
}

// 拆分 前缀.名称
function split_prefix_name(string $name, string $default_prefix, string $needle = '.'): array
{
    $name = trim($name, $needle);
    return str_contains($name, $needle) ? explode($needle, $name, 2) : [$default_prefix, $name];
}

// 解析应用@(.)名称
function parse_app_name(string $name, string $app = ''): array
{
    if (empty($app)) {
        $app = APP_NAME;
    }
    $name = str_replace('@', '.', $name);
    return split_prefix_name($name, $app);
}

// 检查条件是否跳过
function check_if_skip(int $if, array $data, string $field): bool
{
    if ($if === IF_VALUE) {
        return empty($data[$field]);
    }
    if ($if === IF_EMPTY) {
        return !empty($data[$field]);
    }
    if ($if === IF_ISSET) {
        return !isset($data[$field]);
    }
    if ($if === IF_UNSET) {
        return isset($data[$field]);
    }
    return false;
}

// 解析批量函数
function parse_batch_func($func = []): array
{
    if (is_string($func)) {
        $func = explode(',', str_replace('|', ',', $func));
    }
    return array_filter($func);
}

// 选项转换数组
function str_to_array(string $options, string $sep = '|', string $eq = '='): array
{
    if (empty($options)) {
        return [];
    }
    return Tool::str_to_array($options, $sep, $eq);
}

// 对值执行批量函数
function run_batch_func($value, $func)
{
    $func = parse_batch_func($func);
    foreach ($func as $fn) {
        if (function_exists($fn)) {
            $value = is_array($value) ? array_map(fn($v) => $fn(strval($v)), $value) : $fn(strval($value));
        }
    }
    return $value;
}

// =======================框架助手函数===========================
// 获取类单例，不存在则创建
function app(string $class, array $args = []): object
{
    return App::make($class, $args);
}

// 调用命令
function cli(string $uri, string $app = '', bool $isCall = true)
{
    return Cli::run($uri, $app, $isCall);
}

// 配置管理：设置，检测，获取单个，获取全部
function config(string $name = '', $value = '')
{
    $config = Config::init();
    if ('' === $name) {
        return $config->get();
    }
    if ('' === $value) {
        return str_starts_with($name, '?') ? $config->has(substr($name, 1)) : $config->get($name);
    }
    return $config->set($name, $value);
}

// 获取配置(加强)
function config_get(string $name = '', $default = '', bool $to_array = false)
{
    return Config::init()->get($name, $default, $to_array);
}

// 获取站点配置
function site(string $name = '', $default = '', bool $to_array = false)
{
    $config = Config::init();
    if ('' === $name) {
        return $config->get('site');
    }
    return str_starts_with($name, '?') ? $config->has('site.' . substr($name, 1)) : $config->get('site.' . $name, $default, $to_array);
}

// 加载新配置
function config_load(array $load): void
{
    Config::init()->load($load);
}

// 重载配置
function config_reload(): bool
{
    return Config::init()->reload();
}

// 缓存管理：设置，检测，获取，删除
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

// 缓存获取：不存在则创建
function cache_make(string $name, ?Closure $closure = null, int $expire = 0)
{
    return Cache::init()->make($name, $closure, $expire);
}

// 清除缓存：指定路径
function cache_clear(string $path = ''): bool
{
    return Cache::init()->flush($path);
}

// 获取部件(缓存)对象
function widget(string $name): object
{
    [$app, $name] = parse_app_name($name);
    $class = 'app\\' . $app . '\\widget\\' . name_to_camel($name);
    return App::make($class);
}

// 根据标签名重载部件缓存
function widget_reload(string $tag, string $app = ''): void
{
    if (empty($app)) {
        $app = APP_NAME;
    }
    $cache = Cache::init();
    $cache->flush($app . '@widget/' . $tag . '/*');
    $cache->flush('common@widget/' . $tag . '/*');
}

// 字符串加密
function encrypt(string $string, string $salt = ''): string
{
    return Crypt::init()->encrypt($string, $salt);
}

// 字符串解密
function decrypt(string $string, string $salt = ''): string
{
    return Crypt::init()->decrypt($string, $salt);
}

// cookie管理：设置，检测，获取，删除，获取全部，清空
function cookie(?string $name = '', $value = '', array $options = [])
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

// session管理：设置，检测，获取，删除，获取全部，清空
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

// session闪存：用于Ajax设置临时数据，如验证码
function session_flash(string $name = '', $value = '')
{
    return Session::init()->flash($name, $value);
}

// 输入过滤
function input_filter(array $data): array
{
    Filter::init()->input($data);
    return $data;
}

// 获取输入请求并处理
function input(string $name, $default = null, $batchFunc = [])
{
    return Request::init()->getRequest($name, $default, $batchFunc);
}

// url生成
function url(string $route = '', array $params = [], string $suffix = '*'): string
{
    return Route::init()->buildUrl($route, $params, $suffix);
}

// 获取当前控制器
function get_controller(): string
{
    return Route::init()->getController();
}

// 获取当前方法
function get_action(): string
{
    return Route::init()->getAction();
}

// 调用控制器方法
function action(string $uri, array $params = [], string $app = '')
{
    return Route::init()->dispatch($uri, $params, $app);
}

// 获取视图对象
function view(string $file = '', array $vars = [], bool $isCall = false): object
{
    return View::init()->make($file, $vars, $isCall);
}

// 视图传值
function view_with($vars, $value = ''): object
{
    return View::init()->with($vars, $value);
}

// 数据库快速操作
function pdo($config = []): object
{
    return Connection::init($config);
}

// 验证表名
function is_table(string $table): bool
{
    return pdo()->checkTable($table);
}

// 获取数据表对象
function db(string $table = '', $config = []): object
{
    return Db::connect($config, $table);
}

// 获取模型对象
function model(string $name = ''): object
{
    [$app, $name] = parse_app_name($name);
    $class = 'app\\' . $app . '\\model\\' . name_to_camel($name);
    return App::make($class);
}

// 获取验证器对象
function validate(array $validate, array $data = [], bool $isBatch = false): object
{
    return Validate::init()->make($validate, $data, $isBatch);
}

// 调用扩展
function extend(string $name, array $args = [])
{
    $method = 'init';
    if (str_contains($name, '::')) {
        [$name, $method] = explode('::', $name);
    }
    if (str_contains($name, '.')) {
        $name = explode('.', $name);
        $end = array_pop($name);
        $class = '\\extend\\' . implode('\\', $name) . '\\' . ucfirst($end);
    } else {
        $class = '\\extend\\' . $name . '\\' . ucfirst($name);
    }
    if (method_exists($class, $method)) {
        return call_user_func_array([$class, $method], $args);
    }
    return null;
}

// 错误响应输出并中止
function halt($msg = '', int $code = 400, array $params = []): void
{
    Response::halt($msg, $code, $params);
}

// 记录变量到日志
function log_value($vars, string $name = 'var'): void
{
    Log::init()->value($vars, $name);
}

// 记录trace到调试栏
function trace($msg, string $type = 'debug'): void
{
    DebugBar::init()->trace($msg, $type);
}

// =======================加强函数===========================
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

// 时间多久之前
function get_time_ago(int $time): string
{
    $etime = time() - $time;
    if ($etime < 1) {
        return '刚刚';
    }
    $interval = [31536000 => '年前', 2592000 => '个月前', 604800 => '星期前', 86400 => '天前', 3600 => '小时前', 60 => '分钟前', 1 => '秒前'];
    foreach ($interval as $k => $v) {
        $ok = floor($etime / $k);
        if ($ok != 0) {
            return $ok . $v;
        }
    }
    return '刚刚';
}

// 获取网站版本
function site_ver(): string
{
    return APP_DEBUG ? date('YmdHis') : site('version', date('Y-m-d'));
}

// ids过滤转换
function ids_filter(string $ids, bool $to_array = false, bool $gt_0 = true)
{
    $ids = array_filter(explode(',', $ids), 'is_numeric');
    $ids = array_unique($ids);
    if ($gt_0) {
        $ids = array_filter($ids, fn(int $n) => $n > 0);
    }
    ksort($ids);
    return $to_array ? $ids : implode(',', $ids);
}

// 获取前后关联id
function get_prev_next(int $id, array $keys): array
{
    $k = array_search($id, $keys);
    if ($k === false) {
        return [0, 0];
    }
    $prev_id = $keys[$k - 1] ?? 0;
    $next_id = $keys[$k + 1] ?? 0;
    return [$prev_id, $next_id];
}

// 清理html代码
function clear_html(string $string): string
{
    $string = strip_tags($string);
    $string = preg_replace(['/\t/', '/\r\n/', '/\r/', '/\n/'], '', $string);
    return trim($string);
}

// 清除xss脚本
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
    $http = ['Accept: */*', 'Accept-Encoding: gzip,deflate,sdch', 'Accept-Language: zh-CN,zh;q=0.8', 'Connection: close'];
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
function get_ip()
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
function get_int_ip(): int
{
    return ip2long(get_ip());
}