<?php
/*------------------------------------------------------------------
 | 输入过滤类 2024-08-15 by 无念
 |------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
class Filter
{
    use Single;
    protected array $except;
    protected array $auto;

    private function __construct()
    {
        $this->except = Config::init()->get('filter.except_key', []);
        $this->auto = Config::init()->get('filter.auto', []);
        $this->auto = array_filter($this->auto);
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
        if (empty($val) || in_array($key, $this->except)) {
            return;
        }
        if (is_numeric($key) && isset($this->auto['*'])) {
            $func = $this->auto['*'];
            $val = str_contains($func, '|') ? run_batch_func($val, $func) : $func(strval($val));
            return;
        }
        $ok_key = [];
        foreach ($this->auto as $regx => $func) {
            if ($regx == $key || (str_starts_with($regx, '/') && preg_match($regx, $key)) || (!in_array($key, $ok_key) && $regx == '*')) {
                $ok_key[] = $key;
                $val = str_contains($func, '|') ? run_batch_func($val, $func) : $func(strval($val));
            }
        }
    }
}