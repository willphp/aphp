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
    private int $filesize;

    private function __construct($build = '')
    {
        $this->initConst(strtolower(self::parseName($build)));
        Error::init();
        if (!is_dir(APP_PATH . '/controller')) {
            (new Build)->initApp();
        }
    }

    public function boot(): void
    {
        Middleware::init()->globals(); //执行公共中间件
        $res = Route::init()->dispatch();
        Response::output($res, APP_TRACE);
    }

    public static function make(string $class): object
    {
        return self::$instances[$class] ??= new $class;
    }

    private function initConst(string $appName): void
    {
        define('APP_NAME', $appName);
        define('APP_PATH', ROOT_PATH . '/app/' . APP_NAME);
        define('RUNTIME_PATH', ROOT_PATH . '/runtime/' . APP_NAME);
        $app = get_config('app');
        define('VIEW_PATH', !empty($app['view_path'][APP_NAME]) ? ROOT_PATH . '/' . $app['view_path'][APP_NAME] : APP_PATH . '/view');
        define('THEME_ON', !empty($app['theme_on']) && in_array(APP_NAME, $app['theme_on']));
        define('IS_API', !empty($app['api_list']) && in_array(APP_NAME, $app['api_list']));
        define('APP_DEBUG', $app['debug'] ?? false);
        define('APP_TRACE', $app['trace'] ?? false);
        define('URL_REWRITE', $app['url_rewrite'] ?? false);
        define('__WEB__', URL_REWRITE ? strtr($_SERVER['SCRIPT_NAME'], ['/index.php' => '']) : $_SERVER['SCRIPT_NAME']);
        define('__URL__', IS_HTTPS ? 'https://' . $_SERVER['HTTP_HOST'] . __WEB__ : 'http://' . $_SERVER['HTTP_HOST'] . __WEB__);
        define('__HISTORY__', $_SERVER['HTTP_REFERER'] ?? '');
        define('__ROOT__', rtrim(strtr(dirname($_SERVER['SCRIPT_NAME']), '\\', '/'), '/'));
        define('__STATIC__', __ROOT__ . '/static');
        define('__UPLOAD__', __ROOT__ . '/uploads');
    }

    private static function parseName($build = ''): string
    {
        if (empty($build)) {
            return basename($_SERVER['SCRIPT_FILENAME'], '.php');
        }
        if (is_string($build)) {
            return $build;
        }
        if (count($build) == 1) {
            return current($build);
        }
        $host = [$_SERVER['HTTP_HOST'], explode('.', $_SERVER['HTTP_HOST'])[0]];
        foreach ($host as $domain) {
            if (isset($build[$domain])) {
                return $build[$domain];
            }
        }
        return current($build);
    }

    public function getRunInfo(): array
    {
        $this->filesize = 0;
        $run = [];
        $run['path'] = Route::init()->getPath();
        $run['time'] = round((microtime(true) - START_TIME), 4).' s';
        $run['memory'] = number_format((memory_get_usage() - START_MEMORY) / 1024, 2).' KB';
        $files = get_included_files();
        $run['file'] = array_map(fn($v)=>$this->getLoadFile($v), $files);
        $run['total'] = count($files);
        $run['filesize'] = number_format($this->filesize / 1024, 2).' KB';
        return $run;
    }

    protected function getLoadFile(string $file): string
    {
        $len = strlen(ROOT_PATH) + 1;
        $filesize = filesize($file);
        $this->filesize += $filesize;
        return substr($file, $len).'('.number_format($filesize / 1024, 2).' KB)';
    }
}