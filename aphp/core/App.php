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
 * 框架核心类
 */
final class App
{
    use Single;

    // 单例模式
    private static array $instances = []; // 单例列表
    private string $app; // 应用名称
    public string $uri; // URI请求

    private function __construct(array $binds = [])
    {
        $this->app = $this->parseName($binds); // 解析名称
        $this->uri = IS_CLI ? $this->getCliUri() : $this->getHttpUri(); // 获取请求URI
        $this->initConst($this->app); // 初始化常量
        Error::init(); // 初始化错误处理
    }

    // 启动应用
    public function boot(): void
    {
        if (IS_CLI) {
            Cli::run($this->uri, $this->app); // 运行命令行
        } else {
            if ($this->uri != 'error/403?') {
                Middleware::init()->execute('common'); // 运行全局中间件
            }
            $res = Route::init($this->app, $this->uri)->dispatch(); // 获取路由转发返回响应
            Response::output($res, APP_TRACE); // 输出响应
        }
    }

    private function initConst(string $app): void
    {
        define('APP_NAME', $app);
        define('APP_PATH', ROOT_PATH . '/app/' . $app);
        define('RUNTIME_PATH', ROOT_PATH . '/runtime/' . $app);
        // 加载配置
        $config = Config::init([ROOT_PATH . '/config', APP_PATH . '/config', ROOT_PATH . '/.env'])->get('app', []);
        date_default_timezone_set($config['default_timezone'] ?? 'PRC');
        define('APP_DEBUG', (bool)($config['debug'] ?? false));
        define('APP_TRACE', (bool)($config['trace'] ?? false));
        define('URL_REWRITE', (bool)($config['url_rewrite'] ?? false));
        define('IS_API', !empty($config['app_api']) && in_array($app, $config['app_api'], true));
        define('VIEW_PATH', !empty($config['app_view_path'][$app]) ? ROOT_PATH . '/' . $config['app_view_path'][$app] : APP_PATH . '/view');
        define('MULTI_THEME', !empty($config['app_multi_theme']) && in_array($app, $config['app_multi_theme'], true));
        if (!IS_CLI) {
            if (!empty($_SERVER['PATH_INFO'])) {
                $_SERVER['SCRIPT_NAME'] = str_replace($_SERVER['PATH_INFO'], '', $_SERVER['SCRIPT_NAME']);
            }
            $_SERVER['HTTP_HOST'] ??= 'localhost';
            define('__HOST__', (IS_HTTPS ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
            $_SERVER['SCRIPT_NAME'] ??= '';
            define('__WEB__', URL_REWRITE ? str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']) : $_SERVER['SCRIPT_NAME']);
            define('__URL__', __HOST__ . __WEB__);
            define('__HISTORY__', $_SERVER['HTTP_REFERER'] ?? '');
            define('__ROOT__', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));
            define('__STATIC__', __ROOT__ . '/static');
            define('__UPLOAD__', __ROOT__ . '/uploads');
            // 生成应用
            if (!is_dir(APP_PATH)) {
                Cli::run('make:app ' . $app, $app, true);
            }
        }
        if (is_file(APP_PATH.'/common.php')) include APP_PATH.'/common.php'; // 加载应用函数
    }

    // 获取cli请求URI
    private function getCliUri(): string
    {
        if (empty($_SERVER['argv'])) {
            return '';
        }
        $args = array_slice($_SERVER['argv'], 1);
        if (empty($args)) {
            return '';
        }
        $firstArg = strtolower(trim($args[0]));
        if (str_contains($firstArg, '@')) {
            [$app, $command] = explode('@', $firstArg, 2);
            $this->app = trim($app);
            $args[0] = trim($command);
        } else {
            $args[0] = $firstArg;
        }
        return implode(' ', array_filter($args, 'trim'));
    }

    // 获取http请求URI
    private function getHttpUri(): string
    {
        $uri = $_SERVER['PATH_INFO'] ?? $_SERVER['ORIG_PATH_INFO'] ?? $_SERVER['REDIRECT_PATH_INFO'] ?? $_SERVER['REDIRECT_URL'] ?? '';
        if (empty($uri)) {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $uri = str_contains($requestUri, '?') ? strstr($requestUri, '?', true) : $requestUri;
        }
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if ($scriptName && str_starts_with($uri, $scriptName)) {
            $uri = substr($uri, strlen($scriptName));
        }
        $uri = trim($uri, '/');
        if (!empty($uri)) {
            $uri = preg_replace('/[^\w\x7f-\xff\/\-_.%]/u', '', $uri);
            $uri = str_replace('.html', '', $uri);
            $uri = preg_replace('/\/+/', '/', $uri);
        }
        $query = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?: '';
        return $uri . '?' . $query;
    }

    // 解析应用名称
    private function parseName(array $binds = []): string
    {
        $default = 'index';
        if (empty($binds)) {
            return basename($_SERVER['SCRIPT_FILENAME'] ?? $default, '.php');
        }
        if (count($binds) === 1) {
            return current($binds);
        }
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host !== '') {
            $subdomain = strtolower(explode('.', $host, 2)[0]);
            return $binds[$host] ?? $binds[$subdomain] ?? current($binds);
        }
        return current($binds);
    }

    // 获取类单例，不存在则创建
    public static function make(string $class, array $args = []): object
    {		
        if (empty($args)) {
            return self::$instances[$class] ??= new $class;
        }
        $sign = md5(serialize($args));
        return self::$instances[$class][$sign] ??= new $class(...$args);
    }
}