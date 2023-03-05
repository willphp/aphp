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

use Closure;
use ReflectionClass;

class Middleware
{
    use Single;

    protected array $params = [];

    public function globals(): bool
    {
        $middleware = array_unique(get_config('middleware.global', []));
        return empty($middleware) || $this->exec($middleware);
    }

    public function add(string $name, array $middleware)
    {
        $web = get_config('middleware.web.' . $name, []);
        foreach ($middleware as $class) {
            $web[] = $class;
        }
        return Config::init()->set('middleware.web.' . $name, array_unique($web));
    }

    public function web(string $name, array $params = []): bool
    {
        $web = get_config('middleware.web.' . $name, []);
        if (!empty($web)) {
            $this->params = $params;
            return $this->exec($web);
        }
        return true;
    }

    public function ctrlExec(object $controller): void
    {
        $class = new ReflectionClass($controller);
        if ($class->hasProperty('middleware')) {
            $property = $class->getProperty('middleware');
            $property->setAccessible(true);
            $middlewares = $property->getValue($controller);
            if (is_array($middlewares)) {
                foreach ($middlewares as $key => $val) {
                    if (!is_numeric($key)) {
                        $this->set($key, $val);
                    } else {
                        $this->set($val);
                    }
                }
            } else {
                $this->set($middlewares);
            }
        }
    }

    public function set(string $name, array $mode = []): bool
    {
        $exe = []; //执行的控制器中间件
        $middleware = get_config('middleware.controller.' . $name, []); //当前控制器中间件
        if (!$mode) {
            $exe = $middleware;
        } else {
            $action = Route::init()->getAction();
            foreach ($mode as $type => $ctrlList) {
                if ($type == 'only' && in_array($action, $ctrlList)) {
                    $exe = array_merge($exe, $middleware);
                }
                if ($type == 'except' && !in_array($action, $ctrlList)) {
                    $exe = array_merge($exe, $middleware);
                }
            }
        }
        return $this->exec(array_unique($exe));
    }

    public function exec(array $middlewares = []): bool
    {
        $middlewares = array_reverse(array_unique($middlewares));
        $fn = fn(Closure $callback, string $class): Closure => fn() => $this->middlewareRun($callback, $class);
        $dispatcher = array_reduce($middlewares, $fn, fn() => null);
        $dispatcher();
        return true;
    }

    protected function middlewareRun(Closure $callback, string $class)
    {
        if (method_exists($class, 'run')) {
            $content = call_user_func_array([App::make($class), 'run'], [$callback, $this->params]);
            Response::output($content);
        }
    }

}