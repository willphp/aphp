<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
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
        $route = $flip ? $this->rule['flip'] : $this->rule['just'];
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
        return $uri;
    }

    protected function parseRule(string $app): array
    {
        $file = APHP_TOP . '/route/' . $app . '.php';
        $routing = ['just' => [], 'flip' => []];
        $route = file_exists($file) ? include $file : [];
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
            $routing['just'][$k] = trim(strtolower($v), '/');
        }
        $flip = array_flip($routing['just']);
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