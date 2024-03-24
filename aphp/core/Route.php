<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;

use ReflectionMethod;

class Route
{
    use Single;

    protected string $app;
    protected string $controller;
    protected string $action;
    protected array $route;
    protected string $path;

    private function __construct(string $app = '', string $uri = '?')
    {
        $this->app = $app ?: APP_NAME;
        $this->controller = Config::init()->get('route.default_controller', 'index');
        $this->action = Config::init()->get('route.default_action', 'index');
        $this->route = $this->parseRoute($uri);
        $this->controller = $this->route['controller'];
        $this->action = $this->route['action'];
        $this->path = $this->route['path'];
    }

    public function dispatch(string $uri = '', array $params = [], string $app = '')
    {
        $isCall = !empty($uri);
        if (!$isCall && IS_GET && Config::init()->get('view.cache', false) && $cache = Cache::init()->get('view/' . md5($this->path))) {
            return $cache;
        }
        $route = empty($uri) ? $this->route : $this->parseRoute($uri, $params, $app);
        $class = 'app\\' . $route['app'] . '\\controller\\' . name_camel($route['controller']);
        $action = $route['action'];
        $params = $route['params'];
        $path = $route['controller'] . '/' . $route['action'];
        if (!empty($params) && !$isCall) {
            Request::init()->setGet($params);
        }
        if (str_starts_with($action, '_') && !$isCall) {
            $code = substr($action, 1);
            $code = is_numeric($code) ? (int)$code : 405;
            $this->error($code, ['path' => $path]);
        }
        if (!method_exists($class, $action)) {
            if ($isCall) {
                return false;
            }
            if (IS_GET && $view = View::init()->make('', [], true)->toString()) {
                return $view;
            }
            $this->error(404, ['path' => $path]);
        }
        Middleware::init()->execute('framework.controller_start', ['path' => $route['path']]);
        $class = App::make($class);
        $classAction = new ReflectionMethod($class, $action);
        if (!$classAction->isPublic()) {
            if ($isCall) {
                return false;
            }
            $this->error(405, ['path' => $path]);
        }
        $binds = $extend = [];
        $isReq = false;
        $actionArgs = $classAction->getParameters();
        foreach ($actionArgs as $arg) {
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
            } elseif (isset($_POST[$argName])) {
                $binds[$argName] = $_POST[$argName];
            } elseif ($arg->isDefaultValueAvailable()) {
                $binds[$argName] = $extend[$argName] = $arg->getDefaultValue();
            } else {
                if ($isCall) {
                    return false;
                }
                $this->error(416, ['path' => $path, 'param' => $argName]);
            }
        }
        if (!$isCall) {
            if (!empty($extend)) {
                Request::init()->setGet($extend);
            }
            Middleware::init()->controller($class);
            if ($isReq) {
                $binds['req'] = $this->parseReq();
            }
        }
        return $classAction->invokeArgs($class, $binds);
    }

    protected function parseReq(): array
    {
        $req = Request::init()->getRequest();
        if (Config::init()->get('filter.auto_filter_req', true)) {
            Filter::init()->input($req);
        }
        return $req;
    }

    protected function error(int $code, array $args = []): void
    {
        Response::halt('', $code, $args);
    }

    public function getRoute(): array
    {
        return $this->route;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function parseRoute(string $uri = '', array $params = [], string $app = ''): array
    {
        $arg_path = $arg_query = [];
        if (str_contains($uri, '?')) {
            [$uri, $query] = explode('?', $uri, 2);
            parse_str($query, $arg_query);
        }
        if (empty($uri)) {
            $uri = $this->controller . '/' . $this->action;
        }
        if (empty($app)) {
            $app = $this->app;
        }
        $uri = Rewrite::init($app)->replace($uri);
        $uri = trim($uri, '/');
        $route = [];
        $route['is_plugin'] = str_ends_with($app, '@');
        $route['app'] = $app;
        $route['controller'] = $this->controller;
        $route['action'] = $this->action;
        if (!empty($uri)) {
            $path = explode('/', $uri);
            $path_count = count($path);
            if ($path_count == 1) {
                $route['controller'] = array_shift($path);
            } elseif ($path_count >= 2) {
                $route['controller'] = array_shift($path);
                $route['action'] = array_shift($path);
                $over = count($path);
                if ($over > 0) {
                    for ($i = 0; $i < $over; $i += 2) {
                        $arg_path[$path[$i]] = $path[$i + 1] ?? '';
                    }
                }
            }
            $route['controller'] = name_snake($route['controller']);
            if (is_numeric($route['action'])) {
                $route['action'] = '_' . $route['action'];
            }
        }
        $route['params'] = array_merge($arg_path, $arg_query, $params);
        $route['params'] = array_diff($route['params'], ['']); //filter none
        $route['path'] = ($route['is_plugin'] ? $route['app'] : $route['app'] . '/') . $route['controller'] . '/' . $route['action'];
        $route['rewrite'] = $route['controller'] . '/' . $route['action'];
        if (!empty($route['params'])) {
            ksort($route['params']);
            $args_str = http_build_query($route['params']);
            $route['path'] .= '?' . $args_str;
            $route['rewrite'] .= '/' . str_replace(['=', '&'], '/', $args_str);
        }
        return $route;
    }

    public function buildUrl(string $uri = '', array $params = [], string $suffix = '*'): string
    {
        if (in_array($uri, ['', '@', '@/', '/@'])) {
            return __URL__;
        }
        if (false !== filter_var($uri, FILTER_VALIDATE_URL)) {
            return $uri;
        }
        if ($uri == '[back]') {
            return 'javascript:history.back(-1);';
        }
        if ($uri == '[history]') {
            return $_SERVER['HTTP_REFERER'] ?? 'javascript:history.back(-1);';
        }
        if ($suffix == '*') {
            $suffix = Config::init()->get('route.url_auto_suffix', '.html');
        }
        if (str_starts_with($uri, '@')) {
            $suffix = str_contains($uri, '.') ? '' : $suffix;
            return __URL__ . '/' . trim($uri, '@') . $suffix;
        }
        $action = explode('?', $uri);
        if (preg_match('#^[a-zA-Z0-9\-_]+$#', $action[0])) {
            $uri = $this->controller . '/' . $uri;
        }
        $clear_suffix = Config::init()->get('route.url_clear_suffix', '.html');
        $uri = !empty($clear_suffix) ? str_replace($clear_suffix, '', $uri) : $uri;
        [$app, $uri] = parse_app_name($uri, $this->app);
        $route = $this->parseRoute($uri, $params, $app);
        $url = Rewrite::init($route['app'])->replace($route['rewrite'], true);

        return __URL__ . '/' . $url . $suffix;
    }

    public function buildPageUrl(array $params = []): string
    {
        return $this->buildUrl($this->controller . '/' . $this->action, $params);
    }
}