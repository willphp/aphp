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

use Exception;
use ReflectionException;
use ReflectionMethod;

class Route
{
    use Single;

    private string $controller; //当前控制器
    private string $action; //当前方法
    private array $rule; //路由规则
    private string $uri; //当前uri路径
    private array $route; //路由信息
    private string $path; //路由路径
    private int $counter = 0; //fn计数器

    private function __construct()
    {
        $this->controller = get_config('route.default_controller', 'index');
        $this->action = get_config('route.default_action', 'index');
        $this->rule = get_cache('__Route__', fn() => $this->parseRule());
        $this->uri = $this->getUri();
        $this->route = $this->parseRoute($this->uri, $_GET);
        $this->controller = $this->route['controller'];
        $this->action = $this->route['action'];
        $this->path = $this->route['path'];
    }

    private function getViewCache()
    {
        if (IS_GET && get_config('view.cache', false)) {
            return Cache::driver()->get('view.' . md5($this->path));
        }
        return false;
    }

    public function dispatch()
    {
        $viewCache = $this->getViewCache();

        if ($viewCache) {
            return $viewCache;
        }
        $module = APP_NAME;
        $route = $this->getRoute();
        $controller = name_camel($route['controller']);
        $class = 'app\\' . $module . '\\controller\\' . $controller;
        $action = $route['action'];
        $params = $route['params'];
        $path = $route['controller'] . '/' . $route['action'];
        if (str_starts_with($action, '_')) {
            Response::halt('', 405, ['path' => $path]);
        }
        if (!method_exists($class, $action)) {
            if (IS_GET && View::init()->view_check() !== false) {
                return view();
            }
            Response::halt('', 404, ['path' => $path]);
        }
        $class = App::make($class);
        try {
            $class_method = new ReflectionMethod($class, $action);
            if (!$class_method->isPublic()) {
                Response::halt('', 405, ['path' => $path]);
            }
            $method_args = $class_method->getParameters();
            $isReq = false;
            $binds = $extend = [];
            foreach ($method_args as $arg) {
                $arg_name = $arg->getName();
                if ($arg_name == 'req') {
                    $binds['req'] = [];
                    $isReq = true;
                    continue;
                }
                $type = $arg->getType();
                if (isset($params[$arg_name])) {
                    if (!is_null($type)) {
                        settype($params[$arg_name], $type->getName());
                    }
                    $binds[$arg_name] = $params[$arg_name];
                } elseif (!is_null($type) && !$type->isBuiltin()) {
                    $binds[$arg_name] = App::make($type->getName());
                } elseif ($arg->isDefaultValueAvailable()) {
                    $binds[$arg_name] = $extend[$arg_name] = $arg->getDefaultValue();
                } elseif (isset($_POST[$arg_name])) {
                    $binds[$arg_name] = $_POST[$arg_name];
                } else {
                    Response::halt('', 416, ['path' => $path, 'param' => $arg_name]);
                }
            }
            Request::init()->setGet(array_merge($params, $extend));
            Middleware::init()->web('controller_start');
            if (IS_POST) {
                Request::init()->csrf_check();
            }
            Middleware::init()->ctrlExec($class);
            if ($isReq) {
                $binds['req'] = $this->getReq();
            }
            return $class_method->invokeArgs($class, $binds);
        } catch (ReflectionException $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    private function getReq(): array
    {
        $req = Request::init()->getRequest();
        if (get_config('filter.filter_req', false)) {
            $req = Filter::init()->input($req);
        }
        return $req;
    }

    public function getRoute(string $route = ''): array
    {
        return empty($route) ? $this->route : $this->parseRoute($route);
    }

    public function getPath(string $route = ''): string
    {
        return empty($route) ? $this->path : $this->parseRoute($route, [], true);
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    protected function getUri(): string
    {
        $uri = $this->controller . '/' . $this->action;
        $get_var = get_config('route.get_var', 's');
        $path_info = $_GET[$get_var] ?? $_SERVER['PATH_INFO'] ?? '';
        if (isset($_GET[$get_var])) unset($_GET[$get_var]);
        if (!empty($path_info)) {
            $path_info = preg_replace('/\/+/', '/', trim($_SERVER['PATH_INFO'], '/'));
            $check_regex = get_config('route.check_regex', '#^[a-zA-Z0-9\x7f-\xff\%\/\.\-_]+$#');
            if (empty($check_regex) || preg_match($check_regex, $path_info)) {
                $clear_suffix = get_config('route.clear_suffix', '.html');
                $uri = !empty($clear_suffix) ? str_replace($clear_suffix, '', $path_info) : $path_info;
            }
        }
        return $this->ruleReplace($uri, $this->rule['just']);
    }

    protected function ruleReplace(string $uri, array $rule): string
    {
        if (isset($rule[$uri])) return $rule[$uri];
        foreach ($rule as $k => $v) {
            if (preg_match('#^' . $k . '$#i', $uri)) {
                if (str_contains($v, '$') && str_contains($k, '(')) {
                    $v = preg_replace('#^' . $k . '$#i', $v, $uri);
                }
                return $v;
            }
        }
        return $uri;
    }

    public function parseRule(): array
    {
        $file = ROOT_PATH . '/route/' . APP_NAME . '.php';
        $rule = ['just' => [], 'flip' => []];
        $route = file_exists($file) ? include $file : [];
        if (empty($route)) {
            return $rule;
        }
        $alias = get_config('route.alias', [':num' => '[0-9\-]+']);
        $search = array_keys($alias);
        $replace = array_values($alias);
        foreach ($route as $k => $v) {
            if (str_contains($k, ':')) {
                $k = str_replace($search, $replace, $k);
            }
            $k = trim(strtolower($k), '/');
            $rule['just'][$k] = trim(strtolower($v), '/');
        }
        $flip = array_flip($rule['just']);
        foreach ($flip as $k => $v) {
            if (preg_match_all('/\(.*?\)/i', $v, $res)) {
                $pattern = array_map(fn(int $n): string => '/\$\{' . $n . '\}/i', range(1, count($res[0])));
                $k = preg_replace($pattern, $res[0], $k);
                $this->counter = 1;
                $v = preg_replace_callback('/\(.*?\)/i', fn($match) => '${' . $this->counter++ . '}', $v);
            }
            $rule['flip'][$k] = $v;
        }
        return $rule;
    }

    protected function parseRoute(string $uri, array $params = [], bool $getPath = false)
    {
        $args1 = $args2 = []; //参数
        $route = [];
        $route['module'] = APP_NAME;
        $route['controller'] = $this->controller;
        $route['action'] = $this->action;
        if (str_contains($uri, '?')) {
            [$uri, $get] = explode('?', $uri);
            parse_str($get, $args2);
        }
        $uri = trim($uri, '/');
        $path_info = explode('/', $uri);
        $count = count($path_info); //总数
        if ($count == 1) {
            $route['controller'] = array_shift($path_info);
        } elseif ($count >= 2) {
            $route['controller'] = array_shift($path_info);
            $route['action'] = array_shift($path_info);
            $over = count($path_info);
            for ($i = 0; $i < $over; $i += 2) {
                $args1[$path_info[$i]] = $path_info[$i + 1] ?? '';
            }
        }
        $route['controller'] = name_snake($route['controller']);
        $route['params'] = array_merge($args1, $args2, $params);
        $route['path'] = $route['module'] . '/' . $route['controller'] . '/' . $route['action'];
        if (!empty($route['params'])) {
            ksort($route['params']);
            $route['path'] .= '?' . http_build_query($route['params']);
        }
        array_value_case($route);
        return $getPath ? $route['path'] : $route;
    }

    public function buildUrl(string $route = '', array $params = [], string $suffix = '*'): string
    {
        if (in_array($route, ['', '@', '@/', '/@'])) return __URL__;
        if (false !== filter_var($route, FILTER_VALIDATE_URL)) return $route;
        if ($route == '[back]') return 'javascript:history.back(-1);';
        if ($route == '[history]') return $_SERVER['HTTP_REFERER'] ?? 'javascript:history.back(-1);';
        if ($suffix == '*') $suffix = get_config('route.url_suffix', '.html');
        $args = [];
        if (str_contains($route, '?')) {
            [$route, $get] = explode('?', $route);
            parse_str($get, $args);
        }
        $route = trim($route, '/');
        if (empty($route)) {
            $route = $this->controller . '/' . $this->action;
        }
        if (preg_match('#^[a-zA-Z0-9\-_]+$#', $route)) {
            $route = $this->controller . '/' . $route;
        }
        $params = array_merge($args, $params);
        if (!empty($params)) {
            $get_empty = get_config('route.get_filter_empty', true);
            if ($get_empty) {
                $params = array_filter($params); //过滤空值和0
            }
            $params = str_replace(['&', '='], '/', http_build_query($params));
            $route = trim($route . '/' . $params, '/');
        }
        if (str_starts_with($route, '@')) {
            return __URL__ . '/' . trim($route, '@') . $suffix;
        }
        $route = $this->ruleReplace($route, $this->rule['flip']);
        return __URL__ . '/' . $route . $suffix;
    }

    public function pageUrl(array $params = []): string
    {
        return $this->buildUrl($this->controller . '/' . $this->action, $params);
    }
}