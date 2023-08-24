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
/**
 * 框架主程序类
 */
final class App
{
    use Single;

    private static array $instances = []; //单例集合
    private string $name; //当前应用名称

    //从单例集合中获取或生成类
    public static function make(string $class): object
    {
        return self::$instances[$class] ??= new $class;
    }

    //初始化(域名绑定应用)
    private function __construct(array $binds = [])
    {
        $this->name = $this->getName($binds); //获取应用名
        $this->initConst($this->name); //初始化常量并加载配置
        Error::init(); //错误处理初始化
        if (!is_dir(APP_PATH . '/controller')) Build::make(); //初始化生成应用
    }

    //框架启动
    public function boot(): void
    {
        Middleware::init()->execute('common'); //执行全局中间件
        $res = Route::init()->dispatch();
        Response::output($res, APP_TRACE);
    }

    //初始化常量
    private function initConst(string $name): void
    {
        define('APP_NAME', $name); //应用名
        define('APP_PATH', ROOT_PATH . '/app/' . $name); //应用路径
        define('RUNTIME_PATH', ROOT_PATH . '/runtime/' . $name); //运行路径
        $config = Config::init([ROOT_PATH . '/config', APP_PATH . '/config', ROOT_PATH . '/.env'])->get('app', []); //配置加载并获取应用配置
        define('VIEW_PATH', !empty($config['view_path'][$name]) ? ROOT_PATH . '/' . $config['view_path'][$name] : APP_PATH . '/view'); //模板跳径
        define('THEME_ON', !empty($config['theme_on']) && in_array($name, $config['theme_on'])); //多主题开关
        define('IS_API', !empty($config['api_list']) && in_array($name, $config['api_list'])); //是否为api应用
        define('APP_DEBUG', $config['debug'] ?? false); //调试开关
        define('APP_TRACE', $config['trace'] ?? false); //调试栏开关
        define('URL_REWRITE', $config['url_rewrite'] ?? false); //URL重写
        define('__HOST__', IS_HTTPS ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST']); //域名
        define('__WEB__', URL_REWRITE ? strtr($_SERVER['SCRIPT_NAME'], ['/index.php' => '']) : $_SERVER['SCRIPT_NAME']); //相对地址
        define('__URL__', __HOST__ . __WEB__); //当前URL
        define('__HISTORY__', $_SERVER['HTTP_REFERER'] ?? ''); //来源地址
        define('__ROOT__', rtrim(strtr(dirname($_SERVER['SCRIPT_NAME']), '\\', '/'), '/')); //根目录
        define('__STATIC__', __ROOT__ . '/static'); //静态资源目录
        define('__UPLOAD__', __ROOT__ . '/uploads'); //文件上传目录
    }

    //获取应用名
    private function getName(array $binds = []): string
    {
        if (empty($binds)) {
            return basename($_SERVER['SCRIPT_FILENAME'], '.php'); //当前文件名
        }
        if (count($binds) == 1) {
            return current($binds);
        }
        $domain = $_SERVER['HTTP_HOST']; //当前域名
        $prefix = explode('.', $domain)[0]; //二级域名前缀
        return $binds[$domain] ?? $binds[$prefix] ?? current($binds);
    }
}