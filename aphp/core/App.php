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
 * 应用核心类
 */
class App
{
    use Single;

    private static array $instances = []; // 单例列表
    private string $app; // 应用名称
    private string $uri; // URI请求

    // 初始化
    private function __construct(string|array $bind = '')
    {
        $this->app = $this->parseName($bind); // 解析应用名称
        $this->uri = IS_CLI ? $this->getCliUri() : $this->getHttpUri(); // 获取请求URI
        $this->initApp($this->app); // 应用初始化
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

    // 初始化应用
    private function initApp(string $app): void
    {
        define('APP_NAME', $app); // 应用名称
        define('APP_PATH', ROOT_PATH . '/app/' . $app); // 应用路径
        define('RUNTIME_PATH', ROOT_PATH . '/runtime/' . $app); // 应用运行路径
        $config = Config::init([ROOT_PATH . '/config', APP_PATH . '/config', ROOT_PATH . '/.env'])->get('app'); // 加载配置获取app
        date_default_timezone_set($config['default_timezone'] ?? 'PRC'); // 设置时区
        define('APP_DEBUG', (bool)($config['debug'] ?? false)); // 调试模式
        define('APP_TRACE', (bool)($config['trace'] ?? false)); // 调试栏
        define('URL_REWRITE', (bool)($config['url_rewrite'] ?? false)); // URL重写(伪静态)
        define('IS_API', !empty($config['app_api']) && in_array($app, $config['app_api'], true)); // 是否为API
        define('VIEW_PATH', !empty($config['app_view_path'][$app]) ? ROOT_PATH . '/' . $config['app_view_path'][$app] : APP_PATH . '/view'); // 模板路径
        define('MULTI_THEME', !empty($config['app_multi_theme']) && in_array($app, $config['app_multi_theme'], true)); // 是否多主题
        if (!IS_CLI) {
            $_SERVER['SCRIPT_NAME'] ??= '';
            if (!empty($_SERVER['PATH_INFO'])) {
                $_SERVER['SCRIPT_NAME'] = str_replace($_SERVER['PATH_INFO'], '', $_SERVER['SCRIPT_NAME']);
            }
            $_SERVER['HTTP_HOST'] ??= 'localhost';
            define('__HOST__', (IS_HTTPS ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
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
        is_file(APP_PATH . '/common.php') && require APP_PATH . '/common.php'; // 加载应用自定义函数
    }

    // 解析应用名称
    private function parseName(string|array $bind = ''): string
    {
        if (empty($bind)) {
            $app = basename($_SERVER['SCRIPT_FILENAME'] ?? 'index', '.php');
        } elseif (is_string($bind)) {
            $app = $bind;
        } elseif (count($bind) === 1) {
            $app = current($bind);
        } else {
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $subdomain = ($host !== '') ? strtolower(explode('.', $host, 2)[0]) : '';
            $app = $bind[$host] ?? $bind[$subdomain] ?? current($bind);
        }
        return preg_match('/^[A-Za-z]+$/', $app) ? strtolower($app) : 'index';
    }

    // 获取cli请求URI
    private function getCliUri(): string
    {
        array_shift($_SERVER['argv']);
        if (isset($_SERVER['argv'][0])) {
            $cmd = trim(strtolower($_SERVER['argv'][0]), '@');
            if (str_contains($cmd, '@')) {
                [$app, $cmd] = explode('@', $cmd, 2);
                $this->app = $app;
            }
            $_SERVER['argv'][0] = $cmd;
            return implode(' ', $_SERVER['argv']);
        }
        return '';
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