<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
/**
 * 路由重写替换类
 */
class Rewrite
{
    use Single;

    protected array $rule;
    protected int $counter;

    private function __construct(string $app)
    {
        $this->rule = $this->parseRule($app);
    }

    public function replace(string $uri, bool $flip = false): string
    {
        $route = $flip ? $this->rule['flip'] : $this->rule['keep'];
        if (isset($route[$uri])) {
            return $route[$uri];
        }
        foreach ($route as $k => $v) {
            if (preg_match('#^' . $k . '$#i', $uri)) {
                if (str_contains($v, '$') && str_contains($k, '(')) {
                    $v = preg_replace('#^' . $k . '$#i', $v, $uri);
                }
                return $v;
            }
        }
        return trim($uri, '/');
    }

    protected function parseRule(string $app): array
    {
        $file = ROOT_PATH . '/route/' . $app . '.php';
        $routing = ['keep' => [], 'flip' => []];
        $route = file_exists($file) ? include $file : [];
        // 自动重写路由
        $is_auto_rewrite = Config::init()->get('route.is_auto_rewrite', true);
        if ($is_auto_rewrite) {
            $rewrite_rule = Config::init()->get('route.auto_rewrite_rule', []);
            $route += $rewrite_rule;
        }
        if (empty($route)) {
            return $routing;
        }
        $alias = Config::init()->get('route.rule_alias', [':num' => '[0-9\-]+']);
        $aliasKey = array_keys($alias);
        $aliasVal = array_values($alias);
        foreach ($route as $k => $v) {
            if (str_contains($k, ':')) {
                $k = str_replace($aliasKey, $aliasVal, $k);
            }
            $k = trim(strtolower($k), '/');
            $routing['keep'][$k] = trim(strtolower($v), '/');
        }
        $flip = array_flip($routing['keep']);
        foreach ($flip as $k => $v) {
            if (preg_match_all('/\(.*?\)/i', $v, $res)) {
                $pattern = array_map(fn(int $n): string => '/\$\{' . $n . '\}/i', range(1, count($res[0])));
                $k = preg_replace($pattern, $res[0], $k);
                $this->counter = 1;
                $v = preg_replace_callback('/\(.*?\)/i', fn($match) => '${' . $this->counter++ . '}', $v);
            }
            $routing['flip'][$k] = $v;
        }
        return $routing;
    }
}