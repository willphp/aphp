<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;

use ReflectionException;
use ReflectionMethod;

/**
 * 路由类
 */
class Route
{
    use Single;

    protected array $conf; // 路由配置
    protected array $request; // 路由请求

    private function __construct(string $app = '', string $uri = '?')
    {
        $this->conf = Config::init()->get('route', []);
        $this->request = $this->parseRequest($uri, [], $app);
    }

    // 获取路由信息
    public function get(string $name = '', $default = '')
    {
        if (empty($name)) {
            return $this->request;
        }
        return $this->request[$name] ?? $default;
    }

    // 解析路由请求
    public function parseRequest(string $uri = '', array $params = [], string $app = ''): array
    {
        $request = [
            'app' => $app ?: APP_NAME,
            'controller' => $this->conf['default_controller'] ?? 'index',
            'action' => $this->conf['default_action'] ?? 'index',
        ];
        $param_1 = $param_2 = [];
        if (empty($uri) || $uri == '?') {
            $uri = $request['controller'] . '/' . $request['action'];
        } elseif (str_contains($uri, '?')) {
            [$uri, $query] = explode('?', $uri, 2);
            parse_str($query, $param_2);
        }
        $uri = Rewrite::init($app)->replace($uri);
        if (!empty($uri)) {
            $path = explode('/', $uri);
            $path_count = count($path);
            if ($path_count == 1) {
                $request['controller'] = array_shift($path);
            } elseif ($path_count >= 2) {
                $request['controller'] = array_shift($path);
                $request['action'] = array_shift($path);
                $over = count($path);
                if ($over > 0) {
                    for ($i = 0; $i < $over; $i += 2) {
                        $param_1[$path[$i]] = $path[$i + 1] ?? '';
                    }
                }
            }
            $request['controller'] = name_to_snake($request['controller']);
            if (is_numeric($request['action'])) {
                $request['action'] = '_' . $request['action'];
            }
        }
        $request['params'] = array_merge($param_1, $param_2, $params);
        $request['params'] = array_filter($request['params'], fn($v) => $v !== '');
        $request['path'] = $request['app'] . '/' . $request['controller'] . '/' . $request['action'];
        $request['rewrite'] = $request['controller'] . '/' . $request['action'];
        if (!empty($request['params'])) {
            $args_str = http_build_query($request['params']);
            $request['path'] .= '?' . $args_str;
            $request['rewrite'] .= '/' . str_replace(['=', '&'], '/', $args_str);
        }
        return $request;
    }

    // 分发路由
    public function dispatch(string $uri = '', array $params = [], string $app = '')
    {
        $isCall = !empty($uri);
        if (!$isCall && IS_GET && Config::init()->get('view.is_cache', false) && $cache = Cache::init()->get('view/' . md5($this->request['path']))) {
            return $cache;
        }
        $request = empty($uri) ? $this->request : $this->parseRequest($uri, $params, $app);
        $class = 'app\\' . $request['app'] . '\\controller\\' . name_to_camel($request['controller']);
        $action = $request['action'];
        $params = $request['params'];
        $path = $request['controller'] . '/' . $request['action'];
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
            $this->conf['is_empty_jump'] ??= false;
            if ($this->conf['is_empty_jump']) {
                $class = $this->conf['jump_to']['class'] ?? 'app\\index\\controller\\Empty';
                $action = $this->conf['jump_to']['action'] ?? 'empty';
                if (!empty($this->conf['jump_to']['params'])) {
                    $params[$this->conf['jump_to']['params']] = $path;
                }
            }
            if (!method_exists($class, $action)) {
                $this->error(404, ['path' => $path]);
            }
        }
        if (!empty($params) && !$isCall) {
            Request::init()->setGet($params);
        }
        Middleware::init()->execute('framework.controller_start', ['path' => $request['path']]);
        $class = App::make($class);
        try {
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
        } catch (ReflectionException $e) {
            $this->error(500, ['path' => $path], $e->getMessage());
        }
        return null;
    }

    // 解析请求参数
    protected function parseReq(): array
    {
        $req = Request::init()->getRequest();
        if (Config::init()->get('filter.is_filter_req', true)) {
            Filter::init()->input($req);
        }
        return $req;
    }

    // 错误处理
    protected function error(int $code, array $args = [], string $msg = ''): void
    {
        Response::halt($msg, $code, $args);
    }

    // URL生成
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
        if ($uri == '[refresh]') {
            return 'javascript:location.reload();';
        }
        if ($uri == '[history]') {
            return $_SERVER['HTTP_REFERER'] ?? 'javascript:history.back(-1);';
        }
        if ($suffix == '*') {
            $suffix = $this->config['url_auto_suffix'] ?? '.html';
        }
        if (str_starts_with($uri, '@')) {
            $suffix = str_contains($uri, '.') ? '' : $suffix;
            return __URL__ . '/' . trim($uri, '@') . $suffix;
        }
        $action = explode('?', $uri);
        if (preg_match('#^[a-zA-Z0-9\-_]+$#', $action[0])) {
            $uri = $this->request['controller'] . '/' . $uri;
        }
        $clear_suffix = $this->conf['url_clear_suffix'] ?? '.html';
        $uri = !empty($clear_suffix) ? str_replace($clear_suffix, '', $uri) : $uri;
        [$app, $uri] = name_parse($uri, $this->request['app']);
        $request = $this->parseRequest($uri, $params, $app);
        $url = Rewrite::init($request['app'])->replace($request['rewrite'], true);
        if (str_ends_with($url, '/index')) {
            $url = substr($url, 0, -6); // remove index
        }
        return __URL__ . '/' . $url . $suffix;
    }

    // 生成分页URL
    public function buildPageUrl(array $params = []): string
    {
        return $this->buildUrl($this->request['controller'] . '/' . $this->request['action'], $params);
    }
}