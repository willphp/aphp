<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;

use ArrayAccess;
use Iterator;
use ReflectionFunction;
use aphp\core\db\Query;

abstract class Model implements ArrayAccess, Iterator
{
    protected string $table = ''; //表名
    protected string $pk = ''; //主键
    protected array $allowFill = ['*']; //允许填充字段
    protected array $denyFill = []; //禁止填充字段
    protected string $autoTimeType = 'int'; //自动写入时间类型int|date|datetime|timestamp
    protected string $createTime = 'create_time'; //创建时间字段
    protected string $updateTime = 'update_time'; //更新时间字段
    protected array $validate = []; //验证规则
    protected array $auto = []; //自动处理
    protected array $filter = []; //自动过滤字段
    protected array $errors = []; //模型错误信息
    protected bool $isBatch = false; //批量验证
    protected string $showError = 'show'; //错误响应处理show|redirect
    protected string $dbConfig = ''; //数据库配置
    protected object $db; //数据对象
    protected string $prefix; //表前缀
    protected array $data = []; //模型源数据
    protected array $autoData = []; //自动处理后数据
    protected array $saveData = []; //预处理存储数据

    public function __construct()
    {
        if (empty($this->table)) {
            $this->table = name_snake(basename(strtr(get_class($this), '\\', '/')));
        }
        $this->db = Db::connect($this->dbConfig, $this->table);
        if (empty($this->pk)) {
            $this->pk = $this->db->getPk();
        }
        $this->prefix = $this->db->getPrefix();
    }

    //获取表名
    public function getTable(): string
    {
        return $this->table;
    }

    //获取主键
    public function getPk(): string
    {
        return $this->pk;
    }

    //获取表前缀
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    //获取模型源数据
    public function getData(): array
    {
        return $this->data;
    }

    //设置模型数据
    public function setData(array $data): Model
    {
        $this->data = array_merge($this->data, $data);
        $this->autoData = $this->parseAutoFieldData($this->data);
        return $this;
    }

    //自动处理对应字段数据
    protected function parseAutoFieldData(array $data): array
    {
        foreach ($data as $key => $val) {
            $method = 'get' . name_camel($key) . 'Attr';
            if (method_exists($this, $method)) {
                $data['_' . $key] = $this->$method($val, $data);
            }
        }
        return $data;
    }

    //对象数据数组
    final public function toArray(): array
    {
        $data = $this->autoData;
        foreach ($data as $k => $v) {
            if (is_object($v) && method_exists($v, 'toArray')) {
                $data[$k] = $v->toArray();
            }
        }
        return $data;
    }

    //获取错误信息
    public function getError(): array
    {
        return $this->errors;
    }

    //是否操作失败
    public function isFail(): bool
    {
        return !empty($this->errors);
    }

    //错误响应处理
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

    //重置widget缓存
    public function resetWidget(): void
    {
        $cache = Cache::init();
        $cache->flush(APP_NAME . '@widget/' . $this->table . '/*');
        $cache->flush('common@widget/' . $this->table . '/*');
    }

    //模型操作：新增，更新
    final public function save(array $data = [])
    {
        $inAction = $this->getInAction(); //当前操作
        $this->filterFieldFill($data); //1.过滤填充
        //2.自动验证
        if (!$this->autoValidate($inAction)) {
            return $this->respond();
        }
        //3.自动处理
        if (!$this->autoOperation($inAction)) {
            return $this->respond();
        }
        $this->autoFilter($inAction); //4.自动过滤
        $this->autoTime($inAction); //5.自动时间
        $res = false;
        if ($inAction == IN_INSERT) {
            if (isset($this->saveData[$this->pk])) {
                unset($this->saveData[$this->pk]);
            }
            $this->_before_insert($this->saveData);
            if (!empty($this->errors)) {
                return $this->respond();
            }
            $res = $this->db->insertGetId($this->saveData);
            if (is_numeric($res) && $res > 0) {
                $this->setData($this->db->find($res));
                $this->_after_insert(array_merge($this->saveData, $this->data));
                $this->resetWidget(); //重置widget缓存
            }
        } elseif ($inAction == IN_UPDATE) {
            $this->saveData = array_merge($this->data, $this->saveData);
            $this->_before_update($this->saveData);
            if (!empty($this->errors)) {
                return $this->respond();
            }
            $id = $this->data[$this->pk];
            $res = $this->db->where($this->pk, $id)->update($this->saveData);
            if ($res) {
                $before = $this->data;
                $this->setData($this->db->find($id));
                $after = array_merge($this->saveData, $this->data);
                $this->_after_update($before, $after);
                $this->resetWidget(); //重置widget缓存
            }
        }
        $this->saveData = [];
        return $res ? $this : false;
    }

    //模型操作：删除
    final public function del(int $id = 0): bool
    {
        $id = ($id == 0) ? (int)$this->data[$this->pk] : $id;
        if ($id > 0) {
            $data = $this->data;
            $this->_before_delete($data);
            if ($this->db->delete($id)) {
                $this->setData([]);
                $this->_after_delete($data);
                $this->resetWidget(); //重置widget缓存
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

    protected function _after_update(array $before, array $after): void
    {
    }

    protected function _after_delete(array $data): void
    {
    }

    //获取当前操作： 2 新增 3 更新
    final public function getInAction(): int
    {
        if (empty($this->data) && isset($this->saveData[$this->pk])) {
            $this->data[$this->pk] = $this->saveData[$this->pk];
        }
        return empty($this->data[$this->pk]) ? IN_INSERT : IN_UPDATE;
    }

    //验证附加条件
    protected function _validate_map(array $data = []): array
    {
        return [];
    }

    //1.过滤填充字段
    protected function filterFieldFill(array $data): void
    {
        if (empty($this->allowFill) && empty($this->denyFill)) {
            return;
        }
        if (!empty($this->allowFill) && $this->allowFill[0] != '*') {
            $data = Tool::arr_key_filter($data, $this->allowFill, true);
        }
        if (!empty($this->denyFill)) {
            $data = ($this->denyFill[0] == '*') ? [] : Tool::arr_key_filter($data, $this->denyFill);
        }
        $this->saveData = array_merge($this->saveData, $data);
    }

    //2.自动验证字段
    final public function autoValidate(int $inAction): bool
    {
        if (!empty($this->validate)) {
            $this->errors = Validate::init($this)->setAction($inAction)->setMap($this->_validate_map($this->saveData))->make($this->validate, $this->saveData, $this->isBatch)->getError();
            return empty($this->errors);
        }
        return true;
    }

    //3.自动处理字段
    final public function autoOperation(int $inAction): bool
    {
        if (empty($this->auto)) {
            return true;
        }
        $data = &$this->saveData;
        foreach ($this->auto as $auto) {
            $auto[2] ??= 'string'; //处理方式：string field method function
            $auto[3] ??= AT_SET;
            $auto[4] ??= IN_BOTH;
            [$field, $rule, $type, $at, $in] = $auto;
            if (check_at_continue($at, $data, $field)) {
                continue;
            }
            if ($in > IN_BOTH && $in != $this->$inAction) {
                continue;
            }
            $data[$field] ??= '';
            if ($type == 'field') {
                $data[$field] = $data[$rule] ?? ''; //等同字段
            } elseif ($type == 'method') {
                $data[$field] = call_user_func_array([$this, $rule], [$data[$field], $data]);
            } elseif ($type == 'function') {
                $batchFunc = get_batch_func($rule);
                foreach ($batchFunc as $func) {
                    if (!function_exists($func)) {
                        $this->errors[] = $func . ': function does not exist';
                        return false;
                    }
                    $data[$field] = !empty((new ReflectionFunction($func))->getParameters()) ? $func($data[$field]) : $func();
                }
            } else {
                $data[$field] = $rule;
            }
        }
        return true;
    }

    //4.自动过滤字段
    final public function autoFilter(int $inAction): bool
    {
        if (empty($this->filter)) {
            return true;
        }
        $data = &$this->saveData;
        foreach ($this->filter as $filter) {
            $filter[1] ??= AT_SET;
            $filter[2] ??= IN_BOTH;
            [$field, $at, $in] = $filter;
            if (check_at_continue($at, $data, $field)) {
                continue;
            }
            if ($in == $inAction || $in == IN_BOTH) {
                unset($data[$field]);
            }
        }
        return true;
    }

    //5.自动时间(创建更新时间)
    final public function autoTime(int $inAction): void
    {
        $format = ['date' => 'Y-m-d', 'datetime' => 'Y-m-d H:i:s', 'timestamp' => 'Ymd His'];
        $type = $this->autoTimeType;
        if ($type == 'int' || isset($format[$type])) {
            $time = isset($format[$type]) ? date($format[$type], $_SERVER['REQUEST_TIME']) : $_SERVER['REQUEST_TIME'];
            if ($inAction == IN_INSERT && !empty($this->createTime)) {
                $this->saveData[$this->createTime] = $time;
            }
            if (!empty($this->updateTime)) {
                $this->saveData[$this->updateTime] = $time;
            }
        }
    }

    public function __get($name)
    {
        if (isset($this->autoData[$name])) {
            return $this->autoData[$name];
        }
        if (method_exists($this, $name)) {
            return $this->$name();
        }
        return $name;
    }

    public function __set($name, $value)
    {
        $this->saveData[$name] = $value;
        $this->data[$name] = $value;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([new static(), $name], $arguments);
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this, '_before_' . $name)) {
            $this->{'_before_' . $name}($this->data);
        }
        $res = call_user_func_array([$this->db, $name], $arguments);
        if (!empty($res)) {
            $data = is_object($res) ? $res->toArray() : $res;
            if (is_array($data) && method_exists($this, '_after_' . $name)) {
                $this->{'_after_' . $name}($data);
            }
            if ($name == 'find') {
                return $this->setData($data);
            }
            if ($name == 'select') {
                return array_map(fn(array $v): array => $this->parseAutoFieldData($v), $res);
            }
            if ($name == 'paginate') {
                $res->data = array_map(fn(array $v): array => $this->parseAutoFieldData($v), $res->data);
                return $res;
            }
            if ($res instanceof Query) {
                return $this;
            }
        }
        return $res;
    }

    public function offsetGet($offset)
    {
        return $this->autoData[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
        $this->autoData[$offset] = $value;
        $this->saveData[$offset] = $value;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        if (isset($this->data[$offset])) unset($this->data[$offset]);
        if (isset($this->autoData[$offset])) unset($this->autoData[$offset]);
        if (isset($this->saveData[$offset])) unset($this->saveData[$offset]);
    }

    public function rewind()
    {
        reset($this->data);
    }

    public function key()
    {
        return key($this->autoData);
    }

    public function next()
    {
        return next($this->autoData);
    }

    public function current()
    {
        return current($this->autoData);
    }

    public function valid()
    {
        return current($this->autoData);
    }
}