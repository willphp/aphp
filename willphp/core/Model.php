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

use ArrayAccess;
use Iterator;
use ReflectionFunction;
use willphp\core\db\Query;

abstract class Model implements ArrayAccess, Iterator
{
    protected string $table = ''; //表名
    protected string $pk = ''; //主键
    protected string $tablePrefix = ''; //表前缀
    protected string $dbConfig = ''; //数据库连接配置
    protected array $allowFill = ['*']; //允许填充字段
    protected array $denyFill = []; //禁止填充字段
    protected string $autoTimeType = 'int'; //自动写入时间类型int|date|datetime|timestamp
    protected string $createTime = 'create_time'; //创建时间字段
    protected string $updateTime = 'update_time'; //更新时间字段
    protected bool $isBatch = false; //是否批量验证
    protected string $showError = 'show'; //错误响应show|redirect
    protected array $validate = []; //验证规则
    protected array $auto = []; //自动处理
    protected array $filter = []; //自动过滤字段
    protected object $db; //数据库连接对象
    protected array $original = []; //表单预处理数据
    protected array $data = []; //模型数据
    protected array $fields = []; //处理后的展示用数据
    protected array $errors = []; //错误信息

    public function __construct()
    {
        if (empty($this->table)) {
            $this->table = name_snake(basename(strtr(get_class($this), '\\', '/')));
        }
        $this->db = Db::connect($this->dbConfig, $this->table);
        if (empty($this->pk)) {
            $this->pk = $this->db->getPk();
        }
        $this->tablePrefix = $this->db->getPrefix();
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getPk(): string
    {
        return $this->pk;
    }

    public function getPrefix(): string
    {
        return $this->tablePrefix;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): Model
    {
        $this->data = array_merge($this->data, $data);
        $this->fields = $this->getFieldAuto($this->data);
        return $this;
    }

    protected function getFieldAuto(array $data = []): array
    {
        foreach ($data as $key => $val) {
            $method = 'get' . name_camel($key) . 'Attr';
            if (method_exists($this, $method)) {
                $data['_'.$key] = $this->$method($val, $data);
            }
        }
        return $data;
    }

    public function updateWidget(): void
    {
        $cache = Cache::init();
        $cache->flush(APP_NAME . '@widget/' . $this->table . '/*');
        $cache->flush('common@widget/' . $this->table . '/*');
    }

    final public function toArray(): array
    {
        $data = $this->fields;
        foreach ($data as $k => $v) {
            if (is_object($v) && method_exists($v, 'toArray')) {
                $data[$k] = $v->toArray();
            }
        }
        return $data;
    }

    public function getError(): array
    {
        return $this->errors;
    }

    public function isFail(): bool
    {
        return !empty($this->errors);
    }

    protected function respond()
    {
        if (!empty($this->errors)) {
            if ($this->showError == 'show') {
                Response::validate($this->errors);
            } elseif ($this->showError == 'redirect' && __HISTORY__ && !IS_AJAX) {
                header('Location:' . __HISTORY__);
                exit();
            }
            return false;
        }
        return true;
    }

    final public function save(array $data = [])
    {
        $this->fieldFillCheck($data); //字段填充检测
        //自动验证
        if (!$this->autoValidate()) {
            return $this->respond();
        }
        //自动处理
        if (!$this->autoOperation()) {
            return $this->respond();
        }
        $this->autoFilter(); //自动过滤
        $action = $this->handle(); //当前操作
        if (in_array($this->autoTimeType, ['int', 'date', 'datetime', 'timestamp'])) {
            if (!empty($this->updateTime)) {
                $this->original[$this->updateTime] = $this->getFormatTime($this->autoTimeType);
            }
            if ($action == IN_INSERT && !empty($this->createTime)) {
                $this->original[$this->createTime] = $this->getFormatTime($this->autoTimeType);
            }
        }
        $res = false;
        if ($action == IN_UPDATE) {
            $this->original = array_merge($this->data, $this->original);
            $this->_before_update($this->original);
            if (!empty($this->errors)) {
                return $this->respond();
            }
            $res = $this->db->where($this->pk, $this->data[$this->pk])->update($this->original);
            if ($res) {
                $old = $this->data;
                $this->setData($this->db->find($this->data[$this->pk]));
                //更新后置
                $new = array_merge($this->original, $this->data);
                $this->_after_update($old, $new);
                $this->updateWidget(); //更新widget缓存
            }
        } elseif ($action == IN_INSERT) {
            if (isset($this->original[$this->pk])) {
                unset($this->original[$this->pk]);
            }
            $this->_before_insert($this->original);
            if (!empty($this->errors)) {
                return $this->respond();
            }
            $res = $this->db->insertGetId($this->original);
            if (is_numeric($res)) {
                $this->setData($this->db->find($res));
                $this->_after_insert(array_merge($this->original, $this->data));
                $this->updateWidget(); //更新widget缓存
            }
        }
        $this->original = [];
        return $res ? $this : false;
    }

    final public function del(int $id = 0): bool
    {
        $id = ($id == 0) ? (int)$this->data[$this->pk] : $id;
        if ($id > 0) {
            $data = $this->data;
            $this->_before_delete($data);
            if ($this->db->delete($id)) {
                $this->setData([]);
                $this->_after_delete($data);
                $this->updateWidget(); //更新widget缓存
                return true;
            }
        }
        return false;
    }

    protected function _before_insert(array &$data): void
    {
    }

    protected function _before_update(array &$data): void
    {
    }

    protected function _before_delete(array $data): void
    {
    }

    protected function _after_insert(array $data): void
    {
    }

    protected function _after_update(array $old, array $new): void
    {
    }

    protected function _after_delete(array $data): void
    {
    }

    //根据类型获取时间
    protected function getFormatTime(string $type = '')
    {
        //int|date|datetime|timestamp
        $time = $_SERVER['REQUEST_TIME'];
        if ($type == 'date') {
            return date('Y-m-d', $time);
        } elseif ($type == 'datetime') {
            return date('Y-m-d H:i:s', $time);
        } elseif ($type == 'timestamp') {
            return date('Ymd His', $time);
        }
        return $time;
    }

    //字段填充检测
    private function fieldFillCheck(array $data = []): void
    {
        if (empty($this->allowFill) && empty($this->denyFill)) {
            return;
        }
        if (!empty($this->allowFill) && $this->allowFill[0] != '*') {
            $data = Arr::keyFilter($data, $this->allowFill, true);
        }
        if (!empty($this->denyFill)) {
            $data = ($this->denyFill[0] == '*') ? [] : Arr::keyFilter($data, $this->denyFill);
        }
        $this->original = array_merge($this->original, $data);
    }

    //当前操作类型
    final public function handle(): int
    {
        if (empty($this->data) && isset($this->original[$this->pk])) {
            $this->data[$this->pk] = $this->original[$this->pk];
        }
        return empty($this->data[$this->pk]) ? IN_INSERT : IN_UPDATE;
    }

    protected function _unique_where(array $data): array
    {
        return [];
    }

    //自动验证
    final public function autoValidate(): bool
    {
        if (!empty($this->validate)) {
            $this->errors = Validate::init($this)->uniqueWhere($this->_unique_where($this->original))->make($this->validate, $this->original, $this->isBatch)->getError();
            return empty($this->errors);
        }
        return true;
    }

    //自动处理
    final public function autoOperation(): bool
    {
        if (empty($this->auto)) {
            return true;
        }
        $data = &$this->original;
        foreach ($this->auto as $auto) {
            $auto[2] ??= 'string';
            $auto[3] ??= AT_SET;
            $auto[4] ??= IN_BOTH;
            [$field, $rule, $type, $at, $action] = $auto;
            if (is_continue($at, $data, $field)) {
                continue;
            }
            if ($action == $this->handle() || $action == IN_BOTH) {
                if (empty($data[$field])) {
                    $data[$field] = '';
                }
                if ($type == 'method') {
                    $data[$field] = call_user_func_array([$this, $rule], [$data[$field], $data]);
                } elseif ($type == 'function') {
                    $batchFunc = get_batch_func($rule);
                    foreach ($batchFunc as $func) {
                        if (!function_exists($func)) {
                            $this->errors[] = '自动处理失败：' . $func . ' 函数不存在';
                            return false;
                        }
                        $data[$field] = !empty((new ReflectionFunction($func))->getParameters()) ? $func($data[$field]) : $func();
                    }
                } else {
                    $data[$field] = $rule;
                }
            }
        }
        return true;
    }

    //自动过滤字段
    final public function autoFilter(): bool
    {
        if (empty($this->filter)) {
            return true;
        }
        $data = &$this->original;
        foreach ($this->filter as $filter) {
            $filter[1] ??= AT_SET;
            $filter[2] ??= IN_BOTH;
            [$field, $at, $action] = $filter;
            if (is_continue($at, $data, $field)) {
                continue;
            }
            if ($action == $this->handle() || $action == IN_BOTH) {
                unset($data[$field]);
            }
        }
        return true;
    }

    public function __get($name)
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        }
        if (method_exists($this, $name)) {
            return $this->$name();
        }
        return $name;
    }

    public function __set($name, $value)
    {
        $this->original[$name] = $value;
        $this->data[$name] = $value;
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists($this, '_before_' . $name)) {
            $this->{'_before_' . $name}($name);
        }
        $res = call_user_func_array([$this->db, $name], $arguments);
        if (!empty($res)) {
            $data = is_object($res) ? $res->toArray() : $res;
            if (method_exists($this, '_after_' . $name)) {
                $this->{'_after_' . $name}($data);
            }
            if ($name == 'find') {
                return $this->setData($data);
            }
            if ($name == 'select') {
                $res = array_map(fn(array $v): array => $this->getFieldAuto($v), $res);
            }
            if ($name == 'paginate') {
                $res->objData = array_map(fn(array $v): array => $this->getFieldAuto($v), $res->objData);
                return $res;
            }
            if ($res instanceof Query) {
                return $this;
            }
        }
        return $res;
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return call_user_func_array([new static(), $name], $arguments);
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->original[$offset] = $value;
        $this->data[$offset] = $value;
        $this->fields[$offset] = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->fields[$offset] ?? null;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        if (isset($this->original[$offset])) unset($this->original[$offset]);
        if (isset($this->data[$offset])) unset($this->data[$offset]);
        if (isset($this->fields[$offset])) unset($this->fields[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->data);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->fields);
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        return next($this->fields);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->fields);
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return current($this->fields);
    }
}