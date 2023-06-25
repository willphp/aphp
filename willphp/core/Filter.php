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
class Filter
{
    use Single;

    protected array $exceptField = []; //例外字段
    protected array $htmlField = []; //html字段列表
    protected string $htmlFieldLike = ''; //html字段包含
    protected string $funcHtml = ''; //html处理函数
    protected string $funcExceptHtml = ''; //非html处理
    protected array $fieldAuto = []; //字段自动处理
    protected string $funcOut = ''; //模板输出过滤(全部)函数
    protected string $funcOutExceptHtml = ''; //模板输出过滤(html除外)函数

    private function __construct()
    {
        $filter = Config::init()->get('filter', []);
        $this->exceptField = $filter['except_field'] ?? [];
        $this->funcHtml = $filter['func_html'] ?? '';
        $this->funcExceptHtml = $filter['func_except_html'] ?? '';
        $this->htmlField = $filter['html_field'] ?? [];
        $this->htmlFieldLike = $filter['html_field_like'] ?? '';
        $this->fieldAuto = $filter['field_auto'] ?? [];
        $this->funcOut = $filter['func_out'] ?? '';
        $this->funcOutExceptHtml = $filter['func_out_except_html'] ?? '';
    }

    public function input(array &$data): void
    {
        foreach ($data as $key => &$val) {
            if (is_array($val)) {
                $this->input($data[$key]);
                continue;
            }
            $this->filterIn($val, $key);
        }
    }

    public function output(array &$data): void
    {
        foreach ($data as $key => &$val) {
            if (is_array($val)) {
                $this->output($data[$key]);
                continue;
            }
            $this->filterOut($val, $key);
        }
    }

    protected function isHtmlField($field): bool
    {
        $field = strval($field);
        return in_array($field, $this->htmlField) || (!empty($this->htmlFieldLike) && str_contains($field, $this->htmlFieldLike));
    }

    public function filterIn(&$value, $key): void
    {
        if (!empty($value) && !in_array($key, $this->exceptField)) {
            if (!empty($this->funcHtml) && $this->isHtmlField($key)) {
                $value = value_batch_func($value, $this->funcHtml);
            } elseif (!empty($this->funcExceptHtml) && !$this->isHtmlField($key)) {
                $value = value_batch_func($value, $this->funcExceptHtml);
            }
            if (!is_numeric($key) && !empty($this->fieldAuto)) {
                foreach ($this->fieldAuto as $field => $func) {
                    if ($key == $field || in_array($key, explode(',', $field))) {
                        $value = value_batch_func($value, $func);
                    }
                }
            }
        }
    }

    public function filterOut(&$value, $key): void
    {
        if (!empty($value) && is_scalar($value) && !is_numeric($value)) {
            if (!empty($this->funcOutExceptHtml) && !$this->isHtmlField($key)) {
                $value = value_batch_func($value, $this->funcOutExceptHtml);
            }
            if (!empty($this->funcOut)) {
                $value = value_batch_func($value, $this->funcOut);
            }
        }
    }
}