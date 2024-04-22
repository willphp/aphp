<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;

use Closure;

class Validate
{
    use Single;

    protected ?object $model = null;
    protected string $table = '';
    protected string $pk = 'id';
    protected int $inAction = 0; //action： 0 none 1 all 2 insert 3 update
    protected array $map = []; //value where
    protected array $regex = []; //regex
    protected array $errors = []; //error info

    private function __construct(?object $model = null)
    {
        if (!is_null($model)) {
            $this->model = $model;
            $this->table = $model->getTable();
            $this->pk = $model->getPk();
        }
        $this->regex = Config::init()->get('validate', []);
    }

    public function setAction(int $inAction = 0): object
    {
        $this->inAction = $inAction;
        return $this;
    }

    public function setMap(array $map = []): object
    {
        $this->map = $map;
        return $this;
    }

    //format：[field, rule, [tips], [condition], [action]],
    public function make(array $validate, array $data = [], bool $isBatch = false): object
    {
        if (empty($data)) {
            $data = $_POST;
        }
        foreach ($validate as $verify) {
            $verify[2] ??= '';
            $verify[3] ??= ($this->inAction == 0) ? AT_MUST : AT_SET;
            $verify[4] ??= IN_BOTH;
            [$field, $rules, $msgs, $at, $in] = $verify;
            if (check_at_continue($at, $data, $field)) {
                continue;
            }
            if ($in > IN_BOTH && $in != $this->inAction) {
                continue;
            }
            if ($rules instanceof Closure) {
                $value = isset($data[$field]) ? strval($data[$field]) : '';
                if ($rules($value) !== true) {
                    $this->setError($field, $msgs);
                }
            } else {
                $rules = explode('|', $rules);
                $msgs = explode('|', $msgs);
                foreach ($rules as $k => $func) {
                    $msg = $msgs[$k] ?? $msgs[0];
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
            'int' => FILTER_VALIDATE_INT
        ];
        $verify_ok = false;
        if (!is_null($this->model) && method_exists($this->model, $func)) {
            $verify_ok = $this->model->$func($value, $field, $params, $data); //model method
        } elseif (method_exists($this, $func)) {
            $verify_ok = $this->$func($value, $field, $params, $data); //this method
        } elseif (str_starts_with($func, '/')) {
            $verify_ok = (bool)preg_match($func, $value); //regex
        } elseif (isset($this->regex[$func])) {
            $verify_ok = (bool)preg_match($this->regex[$func], $value);//regex
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

    //value exists format: valueExists:table,pk,field
    public function valueExists(string $value, string $field, string $params, array $data): bool
    {
        if (empty($value)) {
            return false;
        }
        $table = $this->table;
        $pk = $this->pk;
        if (!empty($params)) {
            if (!str_contains($params, ',')) {
                $field = $params;
            } else {
                $params = explode(',', $params);
                $table = $params[0];
                $pk = $params[1];
                $field = $params[2] ?? $field;
            }
        }
        if (empty($table)) {
            return false;
        }
        $map = $this->map;
        $map[$field] = $value;
        if (($this->inAction == 0 && isset($data[$pk])) || ($this->inAction == IN_UPDATE && $table == $this->table)) {
            $map[] = [$pk, '<>', $data[$pk]];
        }
        $exists = db($table)->field($pk)->where($map)->find();
        return (bool)$exists;
    }

    public function unique(string $value, string $field, string $params, array $data): bool
    {
        if (empty($value)) {
            return false;
        }
        return !$this->valueExists($value, $field, $params, $data);
    }

    public function required(string $value, string $field, string $params, array $data): bool
    {
        return isset($data[$field]) && trim($data[$field]) !== '';
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

    public function captcha($value, string $field, string $params, array $data): bool
    {
        return isset($data[$field]) && strtoupper($data[$field]) == session_flash('captcha');
    }
}