<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;

use Closure;

/**
 * 验证器
 */
class Validate
{
    use Single;

    protected ?object $model = null; // 模型
    protected string $table = ''; // 模型表
    protected string $pk = 'id'; // 模型表主键
    protected int $scene = 0; // 操作场景
    protected array $where = []; // 验证附加条件
    protected array $regex = []; // 自定义正则
    protected array $errors = []; // 错误信息

    private function __construct(?object $model = null)
    {
        if (!is_null($model)) {
            $this->model = $model;
            $this->table = $model->getTable();
            $this->pk = $model->getPk();
        }
        $this->regex = Config::init()->get('validate', []);
    }

    // 设置操作场景
    public function setScene(int $scene): object
    {
        $this->scene = $scene;
        return $this;
    }

    // 设置验证附加条件
    public function setWhere(array $where): object
    {
        $this->where = $where;
        return $this;
    }

    // validate格式：[字段, 验证规则, [提示], [条件], [场景]],
    public function make(array $validate, array $data = [], bool $isBatch = false): object
    {
        if (empty($data)) {
            $data = $_POST;
        }
        foreach ($validate as $verify) {
            $verify[2] ??= '';
            $verify[3] ??= ($this->scene == 0) ? FV_MUST : FV_ISSET;
            $verify[4] ??= AC_BOTH;
            [$field, $rules, $tips, $if, $scene] = $verify;
            if (field_validate_skip($if, $data, $field)) {
                continue;
            }
            // 修复未设置场景可跳过BUG
            if ($this->scene != 0 && $scene > AC_BOTH && $scene != $this->scene) {
                continue;
            }
            if ($rules instanceof Closure) {
                $value = isset($data[$field]) ? strval($data[$field]) : '';
                if ($rules($value) !== true) {
                    $this->setError($field, $tips);
                }
            } else {
                $rules = explode('|', $rules);
                $tips = explode('|', $tips);
                foreach ($rules as $k => $func) {
                    $msg = $tips[$k] ?? $tips[0];
                    $this->_verify($data, $field, $func, $msg);
                    if (!$isBatch && !empty($this->errors[$field])) break;
                }
            }
            if (!$isBatch && !empty($this->errors[$field])) break;
        }
        return $this;
    }

    protected function _verify(array $data, string $field, string $func, string $msg): void
    {
        $value = isset($data[$field]) ? strval($data[$field]) : '';
        $params = '';
        if (str_contains($func, ':')) {
            [$func, $params] = explode(':', $func);
        }
        $filter_type = [
            'url' => FILTER_VALIDATE_URL,
            'email' => FILTER_VALIDATE_EMAIL,
            'ip' => FILTER_VALIDATE_IP,
            'float' => FILTER_VALIDATE_FLOAT,
            'int' => FILTER_VALIDATE_INT,
            'boolean' => FILTER_VALIDATE_BOOLEAN
        ];
        $alias = ['=' => 'eq', '!=' => 'neq', '<>' => 'neq', '>' => 'gt', '>=' => 'egt', '<' => 'lt', '<=' => 'elt'];
        $verify_ok = false;
        if (!is_null($this->model) && method_exists($this->model, $func)) {
            $verify_ok = $this->model->$func($value, $field, $params, $data); //model method
        } elseif (method_exists($this, $func)) {
            $verify_ok = $this->$func($value, $field, $params, $data); //this method
        } elseif (str_starts_with($func, '/')) {
            $verify_ok = (bool)preg_match($func, $value); //regex
        } elseif (isset($this->regex[$func])) {
            $verify_ok = (bool)preg_match($this->regex[$func], $value);//regex
        } elseif (isset($alias[$func])) {
            $verify_ok = $this->{$alias[$func]}($value, $field, $params, $data); //eq
        } elseif (isset($filter_type[$func])) {
            $verify_ok = (bool)filter_var($value, $filter_type[$func]); //filter_var
        } elseif (function_exists($func)) {
            $verify_ok = (bool)$func($value); //function
        } else {
            $msg = $func . ' validation rule does not exist';
        }
        if (!$verify_ok) {
            $this->setError($field, $msg);
        }
    }

    public function setError(string $field, string $msg = ''): void
    {
        if (empty($msg)) {
            $msg = $field . ' validation failed';
        }
        if (isset($this->errors[$field])) {
            $this->errors[$field] .= '|' . $msg;
        } else {
            $this->errors[$field] = $msg;
        }
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

    // 字段必须存在且非空
    public function required(string $value, string $field, string $params, array $data): bool
    {
        return isset($data[$field]) && trim($value) !== '';
    }

    // 在field值为value时必填
    public function required_if(string $value, string $field, string $params, array $data): bool
    {
        [$data_field, $data_value] = explode(',', $params);
        return !(isset($data[$data_field]) && $data[$data_field] == $data_value) || $this->required($value, $field, $params, $data);
    }

    // 任一field有值时必填
    public function required_with(string $value, string $field, string $params, array $data): bool
    {
        $check = $this->_get_check_data($params, $data);
        return !in_array(1, $check) || $this->required($value, $field, $params, $data);
    }

    // 任一field无值时必填
    public function required_without(string $value, string $field, string $params, array $data): bool
    {
        $check = $this->_get_check_data($params, $data);
        return !in_array(0, $check) || $this->required($value, $field, $params, $data);
    }

    // 所有field有值时必填
    public function required_with_all(string $value, string $field, string $params, array $data): bool
    {
        $count = count(explode(',', $params));
        $check = $this->_get_check_data($params, $data);
        if (count($check) == $count) {
            return in_array(0, $check) || $this->required($value, $field, $params, $data);
        }
        return true;
    }

    // 所有field无值时必填
    public function required_without_all(string $value, string $field, string $params, array $data): bool
    {
        $check = $this->_get_check_data($params, $data);
        return in_array(1, $check) || $this->required($value, $field, $params, $data);
    }

    private function _get_check_data(string $params, array $data): array
    {
        $keys = explode(',', $params);
        $check = arr_key_filter($data, $keys, true);
        return array_map(fn($v) => empty($v) ? 0 : 1, $check);
    }

    // 字母数字下划线
    public function string(string $value, string $field, string $params, array $data): bool
    {
        return (bool)preg_match('/^\w+$/', $value);
    }

    // 纯字母
    public function alpha(string $value, string $field, string $params, array $data): bool
    {
        return (bool)preg_match('/^[A-Za-z]+$/', $value);
    }

    // 字母|数字
    public function alpha_num(string $value, string $field, string $params, array $data): bool
    {
        return (bool)preg_match('/^[A-Za-z0-9]+$/', $value);
    }

    // 字母|数字|-|_
    public function alpha_dash(string $value, string $field, string $params, array $data): bool
    {
        return (bool)preg_match('/^[A-Za-z0-9_\-]+$/', $value);
    }

    // 纯汉字
    public function chs(string $value, string $field, string $params, array $data): bool
    {
        return (bool)preg_match('/^[\x7f-\xff]+$/', $value);
    }

    // 汉字|字母
    public function chs_alpha(string $value, string $field, string $params, array $data): bool
    {
        return (bool)preg_match('/^[A-Za-z\x7f-\xff]+$/', $value);
    }

    // 汉字|字母|数字
    public function chs_alpha_num(string $value, string $field, string $params, array $data): bool
    {
        return (bool)preg_match('/^[A-Za-z0-9\x7f-\xff]+$/', $value);
    }

    // 汉字|字母|数字|-|_
    public function chs_dash(string $value, string $field, string $params, array $data): bool
    {
        return (bool)preg_match('/^[A-Za-z0-9_\-\x7f-\xff]+$/', $value);
    }

    // 纯数字(0~n) 不包含负数和小数点
    public function number(string $value, string $field, string $params, array $data): bool
    {
        return ctype_digit($value);
    }

    // 大于0整型(如id,page)
    public function int_id(string $value, string $field, string $params, array $data): bool
    {
        return (bool)preg_match('/^[1-9]\d*$/', $value);
    }

    // confirmed:[field] 字段的值必须与另一个字段相同，通常用于密码确认
    public function confirmed(string $value, string $field, string $params, array $data): bool
    {
        return !isset($data[$params]) || $value == $data[$params];
    }

    // different:[field] 字段的值必须与另一个字段不同
    public function different(string $value, string $field, string $params, array $data): bool
    {
        return isset($data[$params]) && $value != $data[$params];
    }

    // regex:[pattern] 正则验证 如：regex:/^\d{5,20}$/
    public function regex(string $value, string $field, string $params, array $data): bool
    {
        return (bool)preg_match($params, $value);
    }

    // in:[value1,value2,...] 值必须在指定的值中
    public function in(string $value, string $field, string $params, array $data): bool
    {
        $in = explode(',', $params);
        return in_array($value, $in);
    }

    // not_in:[value1,value2,...] 值不能在指定的值中
    public function not_in(string $value, string $field, string $params, array $data): bool
    {
        $in = explode(',', $params);
        return !in_array($value, $in);
    }

    // 在...之间
    public function between(string $value, string $field, string $params, array $data): bool
    {
        $params = explode(',', $params);
        return $value >= $params[0] && $value <= $params[1];
    }

    // 不在...之间
    public function not_between(string $value, string $field, string $params, array $data): bool
    {
        $params = explode(',', $params);
        return $value < $params[0] || $value > $params[1];
    }

    // 验证字符串长度，支持最小长度和最大长度和固定长度
    public function length(string $value, string $field, string $params, array $data): bool
    {
        if (str_contains($params, ',')) {
            $params = explode(',', $params);
            return mb_strlen($value) >= $params[0] && mb_strlen($value) <= $params[1];
        }
        return mb_strlen($value) == $params;
    }

    // 数字最小值，字符串最小长度
    public function min(string $value, string $field, string $params, array $data): bool
    {
        return is_numeric($value) ? $value >= $params : mb_strlen($value) >= $params;
    }

    // 数字最大值，字符串最大长度
    public function max(string $value, string $field, string $params, array $data): bool
    {
        return is_numeric($value) ? $value <= $params : mb_strlen($value) <= $params;
    }

    // 验证是否在某个有效日期之后
    public function after(string $value, string $field, string $params, array $data): bool
    {
        $params = strtotime($params);
        return strtotime($value) >= $params;
    }

    // 验证是否在某个有效日期之前
    public function before(string $value, string $field, string $params, array $data): bool
    {
        $params = strtotime($params);
        return strtotime($value) <= $params;
    }

    // 验证当前操作是否在某个有效日期之内
    public function expire(string $value, string $field, string $params, array $data): bool
    {
        $params = explode(',', $params);
        $now = time();
        return $now >= strtotime($params[0]) && $now <= strtotime($params[1]);
    }

    // 以...开头
    public function start_with(string $value, string $field, string $params, array $data): bool
    {
        return str_starts_with($value, $params);
    }

    // 以...结尾
    public function end_with(string $value, string $field, string $params, array $data): bool
    {
        return str_ends_with($value, $params);
    }

    // 包含
    public function contains(string $value, string $field, string $params, array $data): bool
    {
        return str_contains($value, $params);
    }

    // 等于
    public function eq(string $value, string $field, string $params, array $data): bool
    {
        if (str_starts_with($params, '_')) {
            $params = substr($params, 1);
            return isset($data[$params]) && $value == $data[$params];
        }
        return $value == $params;
    }

    // 不等于
    public function neq(string $value, string $field, string $params, array $data): bool
    {
        if (str_starts_with($params, '_')) {
            $params = substr($params, 1);
            return isset($data[$params]) && $value != $data[$params];
        }
        return $value != $params;
    }

    // 大于
    public function gt(string $value, string $field, string $params, array $data): bool
    {
        if (str_starts_with($params, '_')) {
            $params = substr($params, 1);
            return isset($data[$params]) && $value > $data[$params];
        }
        return $value > $params;
    }

    // 大于等于
    public function egt(string $value, string $field, string $params, array $data): bool
    {
        if (str_starts_with($params, '_')) {
            $params = substr($params, 1);
            return isset($data[$params]) && $value >= $data[$params];
        }
        return $value >= $params;
    }

    // 小于
    public function lt(string $value, string $field, string $params, array $data): bool
    {
        if (str_starts_with($params, '_')) {
            $params = substr($params, 1);
            return isset($data[$params]) && $value < $data[$params];
        }
        return $value < $params;
    }

    // 小于等于
    public function elt(string $value, string $field, string $params, array $data): bool
    {
        if (str_starts_with($params, '_')) {
            $params = substr($params, 1);
            return isset($data[$params]) && $value <= $data[$params];
        }
        return $value <= $params;
    }

    // 验证码
    public function captcha($value, string $field, string $params, array $data): bool
    {
        return isset($data[$field]) && strtoupper($data[$field]) == session_flash('captcha');
    }

    // 格式：exists:表名.主键,[字段],[附加条件]
    public function exists(string $value, string $field, string $params, array $data): bool
    {
        if (empty($value)) return false;
        $table = $this->table;
        $pk = $this->pk;
        $need_where = [];
        if (!empty($params)) {
            $params = explode(',', $params);
            if (str_contains($params[0], '.')) {
                [$table, $pk] = explode('.', $params[0]);
            } else {
                $table = $params[0];
            }
            $field = $params[1] ?? $field;
            if (isset($params[2])) {
                $need_where = str_to_array($params[2], '&');
                $need_where = array_map(function ($v) use ($data) {
                    if (!str_contains($v, '_')) {
                        return $v;
                    }
                    $field = substr($v, 1);
                    return $data[$field] ?? $field;
                }, $need_where);
            }
        }
        if (empty($table)) return false;
        $where = $this->where;
        $where[$field] = $value;
        if (($this->scene == 0 && isset($data[$pk])) || ($this->scene == AC_UPDATE && $table == $this->table)) {
            $where[] = [$pk, '<>', $data[$pk]];
        }
        $where += $need_where;
        $exists = db($table)->field($pk)->where($where)->find();
        return (bool)$exists;
    }

    // 唯一性验证 unique:user.id,username,cid=1&name=_name
    public function unique(string $value, string $field, string $params, array $data): bool
    {
        if (empty($value)) return false;
        return !$this->exists($value, $field, $params, $data);
    }
}