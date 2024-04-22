<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;

use Closure;
use ReflectionClass;

class Middleware
{
    use Single;

    protected array $params = [];

    //执行中间件
    public function execute($name = [], array $params = []): bool
    {
        $middleware = [];
        if (is_string($name)) {
            $middleware = Config::init()->get('middleware.' . $name, []);
        } elseif (is_array($name)) {
            $middleware = $name;
        }
        if (!empty($middleware)) {
            $this->params = $params;
            return $this->exec($middleware);
        }
        return true;
    }

    //添加应用层中间件
    public function add(string $name, array $middleware)
    {
        $config = Config::init();
        $web = $config->get('middleware.' . $name . $name, []);
        return $config->set('middleware.' . $name, array_merge($web, $middleware));
    }

    //中间件执行过程
    public function exec(array $middleware = []): bool
    {
        $middleware = array_reverse(array_unique($middleware));
        $func = array_reduce($middleware, fn(Closure $callback, string $class): Closure => fn() => $this->run($callback, $class), fn() => null);
        $func();
        return true;
    }

    //运行中件间run方法
    protected function run(Closure $callback, string $class): void
    {
        if (method_exists($class, 'run')) {
            $content = call_user_func_array([App::make($class), 'run'], [$callback, $this->params]);
            Response::output($content);
        }
    }

    //设置控制器中间件
    public function set(string $name, array $types = []): bool
    {
        $middleware = [];
        $all = Config::init()->get('middleware.controller.' . $name, []); //所有控制器中间件
        if (empty($all)) {
            Response::halt('[' . $name . '] middleware is not exists!');
        }
        if (empty($types)) {
            $middleware = $all;
        } else {
            $action = Route::init()->getAction();
            if (isset($types['only']) && in_array($action, $types['only'])) {
                $middleware = array_merge($middleware, $all);
            }
            if (isset($types['except']) && !in_array($action, $types['except'])) {
                $middleware = array_merge($middleware, $all);
            }
        }
        return $this->exec($middleware);
    }

    //解析控制器中的中间件设置
    public function controller(object $controller): void
    {
        $class = new ReflectionClass($controller);
        if ($class->hasProperty('middleware')) {
            $property = $class->getProperty('middleware');
            $property->setAccessible(true);
            $middleware = $property->getValue($controller);
            if (is_array($middleware)) {
                foreach ($middleware as $k => $v) {
                    if (!is_numeric($k)) {
                        $this->set($k, $v);
                    } else {
                        $this->set($v);
                    }
                }
            } else {
                $this->set($middleware);
            }
        }
    }
}