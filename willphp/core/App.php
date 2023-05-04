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

final class App
{
    use Single;
    private static array $instances = []; //单例列表

    private function __construct(array $binds = [])
    {
        $this->initConst($binds);
        Error::init();
        if (!is_dir(APP_PATH . '/controller')) {
            Build::init()->initApp();
        }
    }

    public function boot(): void
    {
        Middleware::init()->execute('common'); //执行全局中间件
        $res = Route::init()->dispatch();
        Response::output($res, APP_TRACE);
    }

    public static function make(string $class): object
    {
        return self::$instances[$class] ??= new $class;
    }

    private function initConst(array $binds = []): void
    {
        define('APP_NAME', $this->getName($binds));
        define('APP_PATH', ROOT_PATH . '/app/' . APP_NAME);
        define('RUNTIME_PATH', ROOT_PATH . '/runtime/' . APP_NAME);
        $app = Config::init([ROOT_PATH . '/config', APP_PATH . '/config', ROOT_PATH . '/.env'])->get('app', []);
        define('VIEW_PATH', !empty($app['view_path'][APP_NAME]) ? ROOT_PATH . '/' . $app['view_path'][APP_NAME] : APP_PATH . '/view');
        define('THEME_ON', !empty($app['theme_on']) && in_array(APP_NAME, $app['theme_on']));
        define('IS_API', !empty($app['api_list']) && in_array(APP_NAME, $app['api_list']));
        define('APP_DEBUG', $app['debug'] ?? false);
        define('APP_TRACE', $app['trace'] ?? false);
        define('URL_REWRITE', $app['url_rewrite'] ?? false);
        define('__HOST__', IS_HTTPS ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST']);
        define('__WEB__', URL_REWRITE ? strtr($_SERVER['SCRIPT_NAME'], ['/index.php' => '']) : $_SERVER['SCRIPT_NAME']);
        define('__URL__', __HOST__ . __WEB__);
        define('__HISTORY__', $_SERVER['HTTP_REFERER'] ?? '');
        define('__ROOT__', rtrim(strtr(dirname($_SERVER['SCRIPT_NAME']), '\\', '/'), '/'));
        define('__STATIC__', __ROOT__ . '/static');
        define('__UPLOAD__', __ROOT__ . '/uploads');
    }

    private function getName(array $binds = []): string
    {
        if (empty($binds)) {
            return basename($_SERVER['SCRIPT_FILENAME'], '.php');
        }
        if (count($binds) == 1) {
            return current($binds);
        }
        $domain = $_SERVER['HTTP_HOST'];
        $domain_prefix = explode('.', $domain)[0];
        return $binds[$domain] ?? $binds[$domain_prefix] ?? current($binds);
    }
}