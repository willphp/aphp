<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
final class App
{
    use Single;
    private static array $instances = [];
    private string $app;
    private string $uri;

    public static function make(string $class, array $args = []): object
    {
        if (empty($args)) {
            return self::$instances[$class] ??= new $class;
        }
        $sign = md5(serialize($args));
        return self::$instances[$class][$sign] ??= new $class($args);
    }

    private function __construct(array $binds = [])
    {
        $this->app = strtolower($this->parseName($binds));
        $this->uri = $this->parseUri();
        $this->initApp($this->app);
        Error::init();
    }

    public function boot(): void
    {
        if (IS_CLI) {
            Cli::run($this->uri, $this->app);
        } else {
            Middleware::init()->execute('common');
            $res = Route::init($this->app, $this->uri)->dispatch();
            Response::output($res, APP_TRACE);
        }
    }

    private function initApp(string $app): void
    {
        define('APP_NAME', $app);
        define('APP_PATH', APHP_TOP . '/app/' . $app);
        define('RUNTIME_PATH', APHP_TOP . '/runtime/' . $app);
        $config = Config::init([APHP_TOP.'/config', APP_PATH.'/config', APHP_TOP.'/.env'])->get('app');
        date_default_timezone_set($config['default_timezone'] ?? 'PRC');
        define('APP_DEBUG', $config['debug'] ?? false);
        define('APP_TRACE', $config['trace'] ?? false);
        define('URL_REWRITE', $config['url_rewrite'] ?? false);
        define('IS_API', !empty($config['api_app']) && in_array($app, $config['api_app']));
        define('VIEW_PATH', !empty($config['view_path'][$app]) ? APHP_TOP . '/' . $config['view_path'][$app] : APP_PATH . '/view');
        define('THEME_ON', !empty($config['theme_on']) && in_array(APP_NAME, $config['theme_on']));
        if (!empty($_SERVER['PATH_INFO'])) {
            $_SERVER['SCRIPT_NAME'] = str_replace($_SERVER['PATH_INFO'], '', $_SERVER['SCRIPT_NAME']);
        }
        define('__HOST__', IS_HTTPS ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST']);
        define('__WEB__', URL_REWRITE ? strtr($_SERVER['SCRIPT_NAME'], ['/index.php' => '']) : $_SERVER['SCRIPT_NAME']);
        define('__URL__', __HOST__ . __WEB__);
        define('__HISTORY__', $_SERVER['HTTP_REFERER'] ?? '');
        define('__ROOT__', rtrim(strtr(dirname($_SERVER['SCRIPT_NAME']), '\\', '/'), '/'));
        define('__STATIC__', __ROOT__ . '/static');
        define('__UPLOAD__', __ROOT__ . '/uploads');
        if (!IS_CLI && !is_dir(APP_PATH)) {
            Cli::run('make:app '.$app, $app, true);
        }
    }

    private function parseName(array $binds = []): string
    {
        if (empty($binds)) {
            return basename($_SERVER['SCRIPT_FILENAME'], '.php');
        }
        if (count($binds) == 1) {
            return current($binds);
        }
        $domain = $_SERVER['HTTP_HOST'];
        $prefix = explode('.', $domain)[0];
        return $binds[$domain] ?? $binds[$prefix] ?? current($binds);
    }

    private function parseUri(): string
    {
        if (IS_CLI) {
            array_shift($_SERVER['argv']);
            if (isset($_SERVER['argv'][0])) {
                $class = trim(strtolower($_SERVER['argv'][0]), '@');
                if (str_contains($class, '@')) {
                    [$app, $class] = explode('@', $class, 2);
                    $this->app = $app;
                }
                $_SERVER['argv'][0] = $class;
                return implode(' ', $_SERVER['argv']);
            }
            return '';
        }
        $uri = $_SERVER['PATH_INFO'] ?? $_SERVER['ORIG_PATH_INFO'] ?? $_SERVER['REDIRECT_PATH_INFO'] ?? $_SERVER['REDIRECT_URL'] ?? '';
        if (empty($uri)) {
            $uri = str_contains($_SERVER['REQUEST_URI'], '?') ? strstr($_SERVER['REQUEST_URI'], '?', true) : $_SERVER['REQUEST_URI'];
        }
        if (!empty($uri) && str_starts_with($uri, $_SERVER['SCRIPT_NAME'])) {
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        }
        $uri = trim($uri, '/');
        if (!empty($uri)) {
            $uri = preg_replace('/[^a-zA-Z0-9.\/\-_]/', '', $uri);
            $uri = str_replace('.html', '', $uri);
            $uri = preg_replace('/\/+/', '/', $uri);
        }
        $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?: '';
        return $uri . '?' . $query;
    }
}