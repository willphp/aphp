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
/**
 * 请求处理类
 */
class Request
{
    use Single;

    protected array $items = []; //get和post数据集合

    private function __construct()
    {
        $this->items['get'] = $_GET;
        $this->items['post'] = $_POST;
    }

    //获取请求数据
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
                $value = Arr::get($this->items, $name, $default);
            } else {
                $value = $this->items['post'][$name] ?? $this->items['get'][$name] ?? $default;
            }
        }
        return empty($batchFunc) ? $value : value_batch_func($value, $batchFunc);
    }

    //设置请求数据
    public function setRequest(string $name, $value = ''): bool
    {
        return Arr::set($this->items, $name, $value);
    }

    //设置get请求的数据
    public function setGet($name, $value = ''): void
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

    //检测请求来源
    public function isDomain(): bool
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = parse_url($_SERVER['HTTP_REFERER']);
            return $referer['host'] == $_SERVER['HTTP_HOST'];
        }
        return false;
    }

    //获取主机
    public function getHost(string $url = ''): string
    {
        if (empty($url)) {
            return $_SERVER['HTTP_HOST'];
        }
        $arr = parse_url($url);
        return $arr['host'] ?? '';
    }

    //获取请求头信息
    public function getHeader(string $name = '', ?string $default = '')
    {
        $server = $_SERVER;
        if (str_contains($_SERVER['SERVER_SOFTWARE'], 'Apache') && function_exists('apache_response_headers')) {
            $response = call_user_func('apache_response_headers');
            $server = array_merge($server, $response);
        }
        $headers = [];
        foreach ($server as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $k = str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($k, 5))));
                $headers[$k] = $v;
            }
        }
        if (empty($name)) {
            return $headers;
        }
        return $headers[strtolower($name)] ?? $default;
    }


    public function csrfCreate(string $name = 'csrf_token'): string
    {
        $serverToken = Session::init()->get($name);
        if (!$serverToken) {
            $serverToken = md5(get_ip() . microtime(true));
            Session::init()->set($name, $serverToken);
        }
        return $serverToken;
    }

    public function csrfCheck(): void
    {
        if (Config::init()->get('view.csrf_check') && $_SERVER['HTTP_HOST'] == $this->getHost()) {
            $serverToken = $this->csrfCreate();
            $clientToken = $this->getHeader('X-CSRF-TOKEN', null) ?? $this->getRequest('csrf_token');
            if ($clientToken != $serverToken) {
                Response::halt('', 412);
            }
        }
    }
}