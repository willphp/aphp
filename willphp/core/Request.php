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
class Request
{
    use Single;

    protected array $items = [];

    private function __construct()
    {
        $this->items['get'] = $_GET;
        $this->items['post'] = $_POST;
        if (empty($_POST)) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            if ($data) {
                $this->items['post'] = $data;
            }
        }
    }

    public function getRequest(string $name = '', $default = null, $batchFunc = [])
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
                [$type, $name] = explode('.', $name);
                $value = $this->items[$type][$name] ?? $default;
            } else {
                $value = $this->items['post'][$name] ?? $this->items['get'][$name] ?? $default;
            }
        }
        return empty($batchFunc) ? $value : value_batch_func($value, $batchFunc);
    }

    public function setRequest(string $name, $value = ''): bool
    {
        return array_dot_set($this->items, $name, $value);
    }

    public function setGet($name, $value = ''): void
    {
        if (is_array($name)) {
            $this->items['get'] = array_merge($this->items['get'], $name);
            $_GET = $this->items['get'];
        } elseif ($value === null) {
            if (isset($this->items['get'][$name])) unset($this->items['get'][$name]);
            if (isset($_GET[$name])) unset($_GET[$name]);
        } else {
            $this->items['get'][$name] = $value;
            $_GET[$name] = $value;
        }
    }

    public function isDomain(): bool
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = parse_url($_SERVER['HTTP_REFERER']);
            return $referer['host'] == $_SERVER['HTTP_HOST'];
        }
        return false;
    }

    public function getHost(string $url = ''): string
    {
        if (empty($url)) {
            return $_SERVER['HTTP_HOST'];
        }
        $arr = parse_url($url);
        return $arr['host'] ?? '';
    }

    public function getHeader($name = '', $default = '')
    {
        $server = $_SERVER;
        if (str_contains($_SERVER['SERVER_SOFTWARE'], 'Apache') && function_exists('apache_response_headers')) {
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

    public function csrf_check(): void
    {
        if (config('view.csrf_check') && $_SERVER['HTTP_HOST'] == $this->getHost()) {
            $serverToken = session('csrf_token');
            if (!$serverToken) {
                $serverToken = md5(get_ip() . microtime(true));
                session('csrf_token', $serverToken);
            }
            $clientToken = $this->getHeader('X-CSRF-TOKEN', null) ?? $this->getRequest('csrf_token');
            if ($clientToken != $serverToken) {
                Response::halt('', 412);
            }
        }
    }

    public function csrf_reset(): void
    {
        if (config('view.csrf_check') && !IS_AJAX) {
            session('csrf_token', null);
        }
    }
}