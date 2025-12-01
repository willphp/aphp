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
 * 输入过滤类
 */
class Filter
{
    use Single;

    protected array $exceptField; // 例外字段
    protected array $filterField; // 过滤字段

    private function __construct()
    {
        $config = Config::init()->get('filter', []);
        $this->exceptField = $config['except_field'] ?? [];
        $this->filterField = array_filter($config['filter_field'] ?? []);
    }

    public function input(array &$data): void
    {
        foreach ($data as $key => &$val) {
            if (is_array($val)) {
                $this->input($data[$key]);
                continue;
            }
            $this->filter_input($val, $key);
        }
    }

    public function filter_input(&$val, $key): void
    {
        if (empty($val) || in_array($key, $this->exceptField)) {
            return;
        }
        if (is_numeric($key) && isset($this->filterField['*'])) {
            $func = $this->filterField['*'];
            $val = str_contains($func, '|') ? exec_batch_func($val, $func) : $func(strval($val));
            return;
        }
        $ok_key = [];
        foreach ($this->filterField as $regx => $func) {
            if ($regx == $key || (str_starts_with($regx, '/') && preg_match($regx, $key)) || (!in_array($key, $ok_key) && $regx == '*')) {
                $ok_key[] = $key;
                $val = str_contains($func, '|') ? exec_batch_func($val, $func) : $func(strval($val));
            }
        }
    }
}