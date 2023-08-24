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
use ReflectionMethod;

class Route
{
    use Single;

    protected string $controller;
    protected string $action;
    protected array $rule;
    protected string $uri;
    protected array $route;
    protected string $path;
    protected int $counter;

    private function __construct()
    {
        $this->controller = Config::init()->get('route.default_controller', 'index');
        $this->action = Config::init()->get('route.default_action', 'index');
        $this->rule = Cache::init()->make('__Route__', fn() => $this->ruleParse());
        $this->uri = $this->getUri();
        $this->route = $this->parseRoute($this->uri, $_GET);
        $this->controller = $this->route['controller'];
        $this->action = $this->route['action'];
        $this->path = $this->route['path'];
    }

    protected function error(int $code, array $errs = []): void
    {
        Response::halt('', $code, $errs);
    }

    public function dispatch()
    {
        if (IS_GET && Config::init()->get('view.cache', false) && $cache = Cache::init()->get('view/' . md5($this->path))) {
            return $cache;
        }
        $class = 'app\\' . APP_NAME . '\\controller\\' . name_camel($this->controller);
        $action = $this->action;
        $params = $this->route['params'];
        $errs = ['path' => $this->controller . '/' . $this->action];
        if (str_starts_with($action, '_')) {
            $code = substr($action, 1);
            $code = is_numeric($code) ? (int) $code : 405;
            $this->error($code, $errs);
        }
        if (!method_exists($class, $action)) {
            if (IS_GET && View::init()->getFile()) {
                return View::init()->make();
            }
            $this->error(404, $errs);
        }

        Middleware::init()->execute('framework.controller_start', ['path' => $this->path]);
        $class = App::make($class);
        $classMethod = new ReflectionMethod($class, $action);
        if (!$classMethod->isPublic()) {
            $this->error(405, $errs);
        }
        $binds = $extend = [];
        $isReq = false;
        $methodArgs = $classMethod->getParameters();
        foreach ($methodArgs as $arg) {
            $argName = $arg->getName();
            if ($argName == 'req') {
                $isReq = true;
                $binds['req'] = [];
                continue;
            }
            $argType = $arg->getType();
            if (isset($params[$argName])) {
                if (!is_null($argType)) {
                    settype($params[$argName], $argType->getName());
                }
                $binds[$argName] = $params[$argName];
            } elseif (!is_null($argType) && !$argType->isBuiltin()) {
                $binds[$argName] = App::make($argType->getName());
            } elseif ($arg->isDefaultValueAvailable()) {
                $binds[$argName] = $extend[$argName] = $arg->getDefaultValue();
            } elseif (isset($_POST[$argName])) {
                $binds[$argName] = $_POST[$argName];
            } else {
                $errs['param'] = $argName;
                $this->error(416, $errs);
            }
        }
        Request::init()->setGet(array_merge($params, $extend));
        if (IS_POST && Config::init()->get('view.csrf_check', false)) {
            Request::init()->csrfCheck();
        }
        Middleware::init()->controller($class);
        if ($isReq) {
            $binds['req'] = $this->getReq();
        }
        return $classMethod->invokeArgs($class, $binds);
    }

    private function getReq(): array
    {
        $req = Request::init()->getRequest();
        if (Config::init()->get('filter.filter_req', false)) {
            Filter::init()->input($req);
        }
        return $req;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getRoute(string $route = ''): array
    {
        return empty($route) ? $this->route : $this->parseRoute($route);
    }

    public function getPath(string $route = ''): string
    {
        return empty($route) ? $this->path : $this->parseRoute($route, [], true);
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
        if (is_numeric($route['action'])) {
            $route['action'] = '_'.$route['action'];
        }
        $route['path'] = $route['module'] . '/' . $route['controller'] . '/' . $route['action'];
        if (!empty($route['params'])) {
            ksort($route['params']);
            $route['path'] .= '?' . http_build_query($route['params']);
        }
        Arr::valueCase($route);
        return $getPath ? $route['path'] : $route;
    }

    protected function ruleParse(): array
    {
        $file = ROOT_PATH . '/route/' . APP_NAME . '.php';
        $rule = ['just' => [], 'flip' => []];
        $route = file_exists($file) ? include $file : [];
        if (empty($route)) {
            return $rule;
        }
        $alias = Config::init()->get('route.alias', [':num' => '[0-9\-]+']);
        $aliasKey = array_keys($alias);
        $aliasVal = array_values($alias);
        foreach ($route as $k => $v) {
            if (str_contains($k, ':')) {
                $k = str_replace($aliasKey, $aliasVal, $k);
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

    protected function ruleReplace(string $uri, array $rule): string
    {
        if (isset($rule[$uri])) {
            return $rule[$uri];
        }
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

    protected function getUri(): string
    {
        $uri = $this->controller . '/' . $this->action;
        $path = $_SERVER['PATH_INFO'] ?? '';
        $pathinfo = trim($path, '/');
        if (!empty($pathinfo)) {
            $pathinfo = preg_replace('/\/+/', '/', $pathinfo);
            $regex = Config::init()->get('route.check_regex', '#^[a-zA-Z0-9\x7f-\xff\%\/\.\-_]+$#');
            if (!empty($regex) && !preg_match($regex, $pathinfo)) {
                exit('非法请求：'. htmlentities($pathinfo));
            }
            $suffix = Config::init()->get('route.clear_suffix', '.html');
            $uri = !empty($suffix) ? str_replace($suffix, '', $pathinfo) : $pathinfo;
        }
        return $this->ruleReplace($uri, $this->rule['just']);
    }

    public function buildUrl(string $route = '', array $params = [], string $suffix = '*'): string
    {
        if (in_array($route, ['', '@', '@/', '/@'])) {
            return __URL__;
        }
        if (false !== filter_var($route, FILTER_VALIDATE_URL)) {
            return $route;
        }
        if ($route == '[back]') {
            return 'javascript:history.back(-1);';
        }
        if ($route == '[history]') {
            return $_SERVER['HTTP_REFERER'] ?? 'javascript:history.back(-1);';
        }
        if ($suffix == '*') {
            $suffix = Config::init()->get('route.url_suffix', '.html');
        }
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
            $get_empty = Config::init()->get('route.get_filter_empty', true);
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