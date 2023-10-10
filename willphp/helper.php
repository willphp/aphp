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
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle !== '' && substr($haystack, -strlen($needle)) === $needle;
    }
}

/**
 * 拆分pre.name
 */
function pre_split(string $name, string $preDefault, string $needle = '.'): array
{
    $name = trim($name, $needle);
    return str_contains($name, $needle) ? explode($needle, $name, 2) : [$preDefault, $name];
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
function is_continue(int $status, array $data, string $field): bool
{
    if ($status == AT_NOT_NULL && empty($data[$field])) {
        return true;
    }
    if ($status == AT_NULL && !empty($data[$field])) {
        return true;
    }
    if ($status == AT_SET && !isset($data[$field])) {
        return true;
    }
    if ($status == AT_NOT_SET && isset($data[$field])) {
        return true;
    }
    return false;
}

/**
 * 记录trace到调试栏
 */
function trace($info = '', string $type = 'debug'): void
{
    \willphp\core\DebugBar::init()->trace($info, $type);
}

/**
 * 记录变量到日志
 */
function log_value($vars, string $name = 'var'): void
{
    \willphp\core\Log::init()->value($vars, $name);
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
 * 获取类单例
 */
function app(string $class): object
{
    return \willphp\core\App::make($class);
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
 * site配置获取和设置
 */
function site(string $name = '', $value = null)
{
    return config($name, $value, 'site');
}

/**
 * 缓存获取和设置
 */
function cache(?string $name = '', $value = '', int $expire = 0)
{
    $cache = \willphp\core\Cache::init();
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
 * 清除指定缓存
 */
function cache_flush(string $prefix = '[app]'): bool
{
    return \willphp\core\Cache::init()->flush($prefix);
}

/**
 * 清除指定Runtime
 */
function clear_runtime(string $app = ''): bool
{
    return \willphp\core\Dir::del(ROOT_PATH.'/runtime/'.$app);
}

/**
 * 清除当前视图编译目录
 */
function clear_view_compile(): bool
{
    return \willphp\core\Dir::del(ROOT_PATH.'/runtime/'.APP_NAME.'/view');
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
    [$module, $name] = pre_split($name, APP_NAME);
    $class = '\\app\\' . $module . '\\model\\' . name_camel($name);
    return app($class);
}

/**
 * 获取部件对象
 */
function widget(string $name): object
{
    [$module, $name] = pre_split($name, APP_NAME);
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
 * 获取当前第几页
 */
function input_page(string $name = 'page'): int
{
    $page = input('get.'.$name, 1, 'intval');
    return max(1,  $page);
}

/**
 * 获取视图对象
 */
function view(string $file = '', array $vars = []): object
{
    return \willphp\core\View::init()->make($file, $vars);
}

/**
 * 视图传值
 */
function view_with($vars, $value = ''): object
{
    return \willphp\core\View::init()->with($vars, $value); //->setTpl()
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
    return \willphp\core\Request::init()->csrfCreate($name);
}

/**
 * 字符串加密
 */
function encrypt(string $string, string $salt = ''): string
{
    return \willphp\core\Crypt::init()->encrypt($string, $salt);
}

/**
 * 字符串解密
 */
function decrypt(string $string, string $salt = ''): string
{
    return \willphp\core\Crypt::init()->decrypt($string, $salt);
}

/**
 * 获取当前控制器
 */
function get_controller(): string
{
    return  \willphp\core\Route::init()->getController();
}

/**
 * 获取当前方法
 */
function get_action(): string
{
    return  \willphp\core\Route::init()->getAction();
}

/**
 * 错误响应
 */
function halt($msg = '', int $code = 400, array $params = []): void
{
    \willphp\core\Response::halt($msg, $code, $params);
}

/**
 * Cookie快捷函数
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
 * Session快捷函数 (用于保存登录信息)
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
 * Session 闪存快捷函数 (用于Ajax设置临时数据，如验证码)
 */
function session_flash($name = '', $value = '')
{
    return \willphp\core\Session::init()->flash($name, $value);
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
 * 获取ip
 */
function get_ip(int $type = 0)
{
    $type = $type ? 1 : 0;
    static $ip = null;
    if (null !== $ip) return $ip[$type];
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
    $ip = $long ? [$ip, $long] : ['0.0.0.0', 0];
    return $ip[$type];
}

/**
 * 格式时间
 */
function get_time_ago(int $time): string
{
    $etime = time() - $time;
    if ($etime < 1) {
        return '刚刚';
    }
    $interval = [31536000 => '年前', 2592000 => '个月前', 604800=>'星期前', 86400=>'天前', 3600=>'小时前', 60=>'分钟前', 1=>'秒前'];
    foreach ($interval as $k => $v) {
        $ok = floor($etime / $k);
        if ($ok != 0) {
            return $ok.$v;
        }
    }
    return '刚刚';
}

/**
 * 字节大小转换
 */
function size_format(int $size): string
{
    return \willphp\core\Dir::sizeFormat($size);
}

/**
 * 获取thumb
 */
function get_thumb(string $image, int $width, int $height, int $thumbType = 6): string
{
    return \willphp\core\Thumb::init()->getThumb($image, $width, $height, $thumbType);
}

/**
 * 数组分页
 */
function get_page(array $list, int $pageSize = 10): array
{
    $count = count($list);
    $page = \willphp\core\Page::init($count, $pageSize);
    $offset = $page->getAttr('offset');
    $list = array_slice($list, $offset, $pageSize);
    return ['list' => $list, 'page_html' => $page->getHtml()];
}