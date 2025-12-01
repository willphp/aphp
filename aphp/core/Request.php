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
 * 请求处理
 */
class Request
{
    use Single;

    protected array $items = [];

    private function __construct()
    {
        $this->items['get'] = $_GET;
        $this->items['post'] = $_POST;
    }

    public function getRequest(string $name = '', mixed $default = null, array|string $batchFunc = []): mixed
    {
        if (empty($name)) {
            $value = array_merge($this->items['get'], $this->items['post']);
        } elseif ($name == 'get.') {
            $value = $this->items['get'];
        } elseif ($name == 'post.') {
            $value = $this->items['post'];
        } else {
            $name = trim($name, '.');
            if (str_contains($name, '.')) {
                $value = arr_get($this->items, $name, $default);
            } else {
                $value = $this->items['post'][$name] ?? $this->items['get'][$name] ?? $default;
            }
        }
        return empty($batchFunc) ? $value : exec_batch_func($value, $batchFunc);
    }

    public function setRequest(string $name, mixed $value = ''): bool
    {
        return arr_set($this->items, $name, $value);
    }

    public function setGet(string|array $name, mixed $value = ''): void
    {
        if (is_array($name)) {
            $this->items['get'] = array_merge($this->items['get'], $name);
            $_GET = $this->items['get'];
        } elseif ($value === null) {
            if (isset($this->items['get'][$name])) {
                unset($this->items['get'][$name]);
            }
            if (isset($_GET[$name])) {
                unset($_GET[$name]);
            }
        } else {
            $this->items['get'][$name] = $value;
            $_GET[$name] = $value;
        }
    }

    public function getHost(string $url = ''): string
    {
        if (empty($url)) return $_SERVER['HTTP_HOST'];
        $arr = parse_url($url);
        return $arr['host'] ?? '';
    }

    public function getHeader(string $name = '', string $default = ''): string|array
    {
        $server = $_SERVER;
        if(str_contains($_SERVER['SERVER_SOFTWARE'], 'Apache') && function_exists('apache_response_headers')) {
            $response = call_user_func('apache_response_headers');
            $server = array_merge($server, $response);
        }
        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($key, 5))))] = $value;
            }
        }
        if (empty($name)) return $headers;
        $name = strtolower($name);
        return $headers[$name] ?? $default;
    }
}