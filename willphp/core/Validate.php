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

use Closure;

class Validate
{
    use Single;

    protected ?object $model = null;
    protected array $uniqueMap = [];
    protected int $handle = 0;
    protected array $errors = []; //错误信息

    private function __construct(?object $model = null)
    {
        if (!is_null($model)) {
            $this->model = $model;
            $this->handle = $model->handle();
        }
    }

    public function make(array $validate, array $data = [], bool $isBatch = false): object
    {
        if (empty($data)) $data = $_POST;
        $regex = Config::init()->get('validate', []); //正则配置
        foreach ($validate as $val) {
            $val[2] ??= ''; //提示信息
            $val[3] ??= ($this->handle == 0) ? AT_MUST : AT_SET; //验证条件
            $val[4] ??= IN_BOTH; //验证时机
            [$field, $rules, $msgs, $at, $action] = $val;
            if (is_continue($at, $data, $field)) {
                continue;
            }
            if ($action > IN_BOTH && $action != $this->handle) {
                continue;
            }
            $this->errors[$field] ??= '';
            $value = isset($data[$field]) ? strval($data[$field]) : '';
            if ($rules instanceof Closure) {
                if ($rules($value) !== true) {
                    $this->errors[$field] = $msgs;
                }
            } else {
                $rules = explode('|', $rules); //规则列表
                $msgs = explode('|', $msgs); //错误提示
                foreach ($rules as $k => $rule) {
                    $msg = $msgs[$k] ?? $msgs[0]; //提示
                    $rule = explode(':', $rule);
                    $rule[1] ??= '';
                    [$func, $params] = $rule; //方法与参数
                    $flag = false;
                    if ($this->model && method_exists($this->model, $func)) {
                        $flag = $this->model->$func($value, $field, $params, $data); //模型方法
                    } elseif (method_exists($this, $func)) {
                        $flag = $this->$func($value, $field, $params, $data); //类方法
                    } elseif (str_starts_with($func, '/')) {
                        $flag = (bool)preg_match($func, $value); //正则表达式
                    } elseif (array_key_exists($func, $regex)) {
                        $flag = (bool)preg_match($regex[$func], $value);//正则配置
                    } elseif (in_array($func, ['url', 'email', 'ip', 'float', 'int', 'bool'])) {
                        $flag = $this->filterVar($value, $field, $func, $data); //filter_var
                    } elseif (function_exists($func)) {
                        $flag = $func($value); //内置函数
                    } else {
                        $msg = $func . ' 验证方法不存在';
                    }
                    if (empty($msg)) {
                        $msg = 'not_msg';
                    }
                    if (!$flag) $this->errors[$field] .= '|' . $msg;
                    $this->errors[$field] = trim($this->errors[$field], '|');
                    if (!$isBatch && !empty($this->errors[$field])) break;
                }
            }
            if (!$isBatch && !empty($this->errors[$field])) break;
        }
        $this->errors = array_filter($this->errors);
        return $this;
    }

    public function getError(): array
    {
        return $this->errors;
    }

    public function isFail(): bool
    {
        return !empty($this->errors);
    }

    public function show(): object
    {
        if (!empty($this->errors)) {
            Response::validate($this->errors);
        }
        return $this;
    }

    public function uniqueWhere(array $map = []): object
    {
        $this->uniqueMap = $map;
        return $this;
    }

    public function unique(string $value, string $field, string $params, array $data): bool
    {
        if (!empty($params) && str_contains($params, ',')) {
            [$table, $pk] = explode(',', $params, 2);
        } elseif (!is_null($this->model)) {
            $table = $this->model->getTable();
            $pk = $this->model->getPk();
        } else {
            return false;
        }
        $map = $this->uniqueMap;
        $map[$field] = $value;
        if (($this->handle == 0 && isset($data[$pk])) || $this->handle == IN_UPDATE) {
            $map[] = [$pk, '<>', $data[$pk]];
        }
        $isFind = db($table)->field($pk)->where($map)->find();
        return !$isFind || empty($value);
    }

    public function required(string $value, string $field, string $params, array $data): bool
    {
        return !empty($data[$field]);
    }

    public function exists(string $value, string $field, string $params, array $data): bool
    {
        return isset($data[$field]);
    }

    public function notExists(string $value, string $field, string $params, array $data): bool
    {
        return !isset($data[$field]);
    }

    public function confirm(string $value, string $field, string $params, array $data): bool
    {
        return !isset($data[$params]) || $value == $data[$params];
    }

    public function regex(string $value, string $field, string $params, array $data): bool
    {
        return (bool)preg_match($params, $value);
    }

    public function filterVar($value, string $field, string $params, array $data): bool
    {
        $params = strtolower($params);
        $types = [];
        $types['url'] = FILTER_VALIDATE_URL;
        $types['email'] = FILTER_VALIDATE_EMAIL;
        $types['ip'] = FILTER_VALIDATE_IP;
        $types['float'] = FILTER_VALIDATE_FLOAT;
        $types['int'] = FILTER_VALIDATE_INT;
        return isset($types[$params]) && filter_var($value, $types[$params]);
    }

    public function captcha($value, string $field, string $params, array $data): bool
    {
        return isset($data[$field]) && strtoupper($data[$field]) == session_flash('captcha');
    }
}