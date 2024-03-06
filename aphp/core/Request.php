<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
class Request
{
    use Single;
    protected array $items = [];

    private function __construct()
    {
        $this->items['get'] = $_GET;
        $this->items['post'] = $_POST;
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
                $value = Tool::arr_get($this->items, $name, $default);
            } else {
                $value = $this->items['post'][$name] ?? $this->items['get'][$name] ?? $default;
            }
        }
        return empty($batchFunc) ? $value : value_batch_func($value, $batchFunc);
    }

    public function setRequest(string $name, $value = ''): bool
    {
        return Tool::arr_set($this->items, $name, $value);
    }

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
}