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
use aphp\core\Validate;
use aphp\core\View;

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
    [$app, $name] = name_parse($name);
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
    return Route::init()->get('controller');
}

// 获取当前方法
function get_action(): string
{
    return Route::init()->get('action');
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
    [$app, $name] = name_parse($name);
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
    if (class_exists($class)) {
        return app($class, $args);
    }
    return NULL;
}

// 输出错误响应并中止
function halt(string $msg = '', int $code = 400, array $params = []): never
{
    Response::halt($msg, $code, $params);
}

// 记录打印变量日志
function log_dump($vars, string $name = 'var'): void
{
    Log::init()->dump($vars, $name);
}

// 记录信息到调试栏
function trace($msg, string $type = 'debug'): void
{
    DebugBar::init()->trace($msg, $type);
}

// 获取网站版本
function site_ver(): string
{
    return APP_DEBUG ? date('YmdHis') : site('version', date('Y-m-d'));
}

// 返回表单令牌值
function csrf_token(string $name = 'csrf_token'): string
{
    return session($name);
}

// 返回from中的csrf字段
function csrf_field(string $name = 'csrf_token'): string
{
    return "<input type='hidden' name='".$name."' value='".csrf_token($name)."'/>\r\n";
}