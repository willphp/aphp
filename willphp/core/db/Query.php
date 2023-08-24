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

namespace willphp\core\db;

use ArrayAccess;
use Exception;
use Iterator;
use PDO;
use PDOStatement;
use willphp\core\Cache;
use willphp\core\Config;
use willphp\core\DebugBar;
use willphp\core\Log;
use willphp\core\Middleware;
use willphp\core\Page;
use willphp\core\Session;
use willphp\core\Single;

class Query implements ArrayAccess, Iterator
{
    use Single;

    protected object $connection;
    protected object $builder;
    protected string $table = '';
    protected string $prefix;
    protected ?array $fieldList = []; //表字段列表
    protected string $pk = 'id'; //表主键
    protected array $options = [];
    protected array $bind = [];
    protected array $objData = []; //对象数据
    protected ?object $page = null;

    private function __construct(string $table = '', $config = [])
    {
        $this->connection = Connection::init($config);
        $this->prefix = $this->connection->getConfig('db_prefix');
        if (!empty($table)) {
            $this->fieldList = $this->getFieldList($table);
            $this->pk = $this->fieldList['pri'] ?? 'id';
            $this->table = $table;
        }
        $this->builder = Builder::init($this->connection, $this);
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function __sleep()
    {
        return ['table'];
    }

    public function getTable(): string
    {
        $table = $this->getOptions('table');
        return $table ?: $this->table;
    }

    public function getOptions(string $name = '')
    {
        if ('' === $name) {
            return $this->options;
        }
        return $this->options[$name] ?? null;
    }

    private function recordSql(string $sql, array $bind = [], bool $isUpdate = false): void
    {
        $sql = $this->getRealSql($sql, $bind);
        if (APP_TRACE) {
            DebugBar::init()->trace($sql, 'sql');
        }
        if ($isUpdate && Config::init()->get('app.log_execute_sql', false)) {
            Log::init()->record($sql, 'sql');
        }
        $middleware = $isUpdate ? 'database_execute' : 'database_query';
        Middleware::init()->execute('framework.'.$middleware, ['sql' => $sql]);
        if ($isUpdate && Config::init()->get('view.csrf_check')) {
            Session::init()->del('csrf_token');
        }
    }

    public function query(string $sql, array $bind = [], bool $getPdo = false)
    {
        $this->recordSql($sql, $bind);
        return $this->connection->query($sql, $bind, $getPdo);
    }

    public function execute(string $sql, array $bind = []): int
    {
        $this->recordSql($sql, $bind, true);
        return $this->connection->execute($sql, $bind);
    }

    public function getFieldList(string $table = ''): array
    {
        if (empty($table)) {
            $table = $this->getTable();
        }
        if ($table == $this->table) {
            return $this->fieldList;
        }
        return Cache::init()->make('field/' . $table . '_field', fn() => $this->parseFieldList($table));
    }

    private function parseFieldList(string $table): array
    {
        $sql = 'show columns from ' . $this->prefix . $table;
        $result = $this->query($sql);
        $data = [];
        foreach ((array)$result as $res) {
            if ($res['Key'] == 'PRI' && $res['Extra'] == 'auto_increment') {
                $data['pri'] = $res['Field'];
            } else {
                $data[] = $res['Field'];
            }
        }
        return $data;
    }


    public function getPk(string $table = ''): string
    {
        if (empty($table)) {
            $table = $this->getTable();
        }
        if ($table == $this->table) {
            return $this->pk;
        }
        $sql = 'show columns from ' . $this->prefix . $table . ' where `Key` = "PRI" and `Extra` ="auto_increment"';
        $result = $this->query($sql);
        return $result[0]['Field'] ?? 'id';
    }

    public function getFilterData(array $data, string $table = '', bool $getSql = false): array
    {
        $fields = $this->getFieldList($table);
        if (!$getSql) {
            unset($fields['pri']);
        }
        return array_filter($data, fn($k)=>in_array($k, $fields), ARRAY_FILTER_USE_KEY);
    }

    public function parseExpress(): array
    {
        $options = $this->options;
        if (empty($options['table'])) {
            $options['table'] = $this->table;
        }
        if (empty($options['table'])) {
            throw new Exception('The query table is not set!');
        }
        $options['field'] ??= '*';
        $options['data'] ??= [];
        $options['where'] ??= [];
        $options['order'] ??= [];
        $options['lock'] ??= false;
        $options['distinct'] ??= false;
        $options['sql'] ??= false;
        $options['obj'] ??= false;
        $params = ['join', 'union', 'group', 'having', 'limit', 'force', 'comment', 'extra', 'using', 'duplicate'];
        foreach ($params as $name) {
            $options[$name] ??= '';
        }
        $this->options = [];
        return $options;
    }

    public function __call($method, $params)
    {
        return call_user_func_array([$this->connection, $method], $params);
    }

    protected function getRealSql(string $sql, array $bind = []): string
    {
        return empty($bind) ? $sql : $this->connection->getRealSql($sql, $bind);
    }

    public function getBind(): array
    {
        $bind = $this->bind;
        $this->bind = [];
        return $bind;
    }

    public function isBind(string $key): bool
    {
        return isset($this->bind[$key]);
    }

    public function bind($key, $value = false, int $type = PDO::PARAM_STR): object
    {
        if (is_array($key)) {
            $this->bind = array_merge($this->bind, $key);
        } else {
            $this->bind[$key] = [$value, $type];
        }
        return $this;
    }

    public function select()
    {
        $options = $this->parseExpress();
        $sql = $this->builder->select($options);
        $bind = $this->getBind();
        if ($options['sql']) {
            return $this->getRealSql($sql, $bind);
        }
        return $this->query($sql, $bind, $options['obj']);
    }

    public function find($id = 0)
    {
        if ($id > 0) {
            $this->where($this->getPk(), $id);
        }
        $result = $this->limit(1)->select();
        if (is_string($result) || $result instanceof PDOStatement) {
            return $result;
        }
        return $result[0] ?? [];
    }

    public function paginate(int $pageSize = 0, int $showNum = 0)
    {
        $options = $this->options;
        $total = $this->count();
        $this->page = Page::init($total, $pageSize, $showNum);
        $this->options = $options;
        $this->options['limit'] = $this->page->getLimit();
        $res = $this->select();
        if (!is_array($res)) {
            return $res;
        }
        $this->objData = $res;
        return $this;
    }

    public function getAttr(string $type = '')
    {
        return $this->page->getAttr($type);
    }

    public function links(): ?object
    {
        return $this->page;
    }

    public function toArray(): array
    {
        return $this->objData;
    }

    public function value(string $field)
    {
        $result = $this->field($field)->find();
        if (is_string($result) || $result instanceof PDOStatement) {
            return $result;
        }
        return $result[$field] ?? '';
    }

    public function column(string $fields, string $key = '')
    {
        $fieldHasKey = true;
        $getOne = false;
        if ($fields != '*') {
            $fields = explode(',', $fields);
            $getOne = 1 == count($fields);
            if (!empty($key) && !in_array($key, $fields)) {
                $fields[] = $key;
                $fieldHasKey = false;
            }
        }
        $result = $this->field($fields)->select();
        if (is_string($result) || $result instanceof PDOStatement) {
            return $result;
        }
        $data = [];
        foreach ($result as $k => $row) {
            $k = !empty($key)? $row[$key] : $k;
            if (!$fieldHasKey) {
                unset($row[$key]);
            }
            $data[$k] = $getOne ? current($row) : $row;
        }
        return $data;
    }

    public function getResult(string $sql): array
    {
        $result = $this->query($sql);
        if (isset($result[0]['Variable_name'])) {
            $data = [];
            foreach ($result as $re) {
                $data[$re['Variable_name']] = $re['Value'];
            }
            return $data;
        }
        return $result;
    }

    public function delete($ids = [])
    {
        if (!empty($ids)) {
            $pk = $this->getPk();
            if (is_numeric($ids)) {
                $this->where($pk, $ids);
            } else {
                $this->where($pk, 'in', $ids);
            }
        }
        $options = $this->parseExpress();
        if (empty($options['where'])) {
            throw new Exception('The delete operation query condition cannot be empty!');
        }
        $sql = $this->builder->delete($options);
        $bind = $this->getBind();
        if ($options['sql']) {
            return $this->getRealSql($sql, $bind);
        }
        return $this->execute($sql, $bind);
    }

    public function update(array $data = [])
    {
        $options = $this->parseExpress();
        if (empty($options['where'])) {
            throw new Exception('The update operation query condition cannot be empty!');
        }
        $data = array_merge($options['data'], $data);
        $data = $this->getFilterData($data, $options['table'], $options['sql']);
        $sql = $this->builder->update($data, $options);
        if (!$sql) {
            throw new Exception('The generated query statement is empty!');
        }
        $bind = $this->getBind();
        if ($options['sql']) {
            return $this->getRealSql($sql, $bind);
        }
        return $this->execute($sql, $bind);
    }

    public function setField($field, $value)
    {
        return $this->data($field, $value)->update();
    }

    public function setInc($field, int $step = 1)
    {
        return $this->inc($field, $step)->update();
    }

    public function setDec($field, int $step = 1)
    {
        return $this->dec($field, $step)->update();
    }

    public function insert(array $data = [], bool $getLastInsID = false, bool $replace = false)
    {
        $options = $this->parseExpress();
        $data = array_merge($options['data'], $data);
        $data = $this->getFilterData($data, $options['table'], $options['sql']);
        $sql = $this->builder->insert($data, $options, $replace);
        $bind = $this->getBind();
        if ($options['sql']) {
            return $this->getRealSql($sql, $bind);
        }
        $result = empty($sql) ? false : $this->execute($sql, $bind);
        if ($result && $getLastInsID) {
            $pk = $this->getPk($options['table']);
            return $this->connection->getInsertId($pk);
        }
        return $result;
    }

    public function replace(array $data = [], bool $getLastInsID = false)
    {
        return $this->insert($data, $getLastInsID, true);
    }

    public function insertGetId(array $data = [])
    {
        return $this->insert($data, true);
    }

    public function insertAll(array $data = [], bool $replace = false)
    {
        $options = $this->parseExpress();
        if (!is_array($data)) {
            return false;
        }
        $sql = $this->builder->insertAll($data, $options, $replace);
        $bind = $this->getBind();
        if ($options['sql']) {
            return $this->getRealSql($sql, $bind);
        }
        return $this->execute($sql, $bind);
    }

    public function total(string $field, string $type = 'count')
    {
        $alias = 'willphp_' . strtolower($type);
        $type = strtoupper($type);
        if (!in_array($type, ['COUNT', 'SUM', 'MIN', 'MAX', 'AVG'])) {
            $type = 'COUNT';
        }
        $options = [];
        $options['table'] = $this->options['table'] ?? '';
        $options['where'] = $this->options['where'] ?? [];
        $options['sql'] = $this->options['sql'] ?? false;
        $this->options = $options;
        $res = $this->field($type . '(' . $field . ') AS ' . $alias)->find();
        if (is_string($res)) {
            return $res;
        }
        return $res[$alias] ?? 0;
    }

    public function count(string $field = '*')
    {
        return $this->total($field);
    }

    public function sum(string $field)
    {
        return $this->total($field, 'sum');
    }

    public function max(string $field)
    {
        return $this->total($field, 'max');
    }

    public function min(string $field)
    {
        return $this->total($field, 'min');
    }

    public function avg(string $field)
    {
        return $this->total($field, 'avg');
    }

    public function data($field, $value = null): object
    {
        if (is_array($field)) {
            $this->options['data'] = isset($this->options['data']) ? array_merge($this->options['data'], $field) : $field;
        } else {
            $this->options['data'][$field] = $value;
        }
        return $this;
    }

    public function inc($field, int $step = 1): object
    {
        $fields = is_string($field) ? explode(',', $field) : $field;
        foreach ($fields as $field) {
            $this->data($field, ['inc', $step]);
        }
        return $this;
    }

    public function dec($field, int $step = 1): object
    {
        $fields = is_string($field) ? explode(',', $field) : $field;
        foreach ($fields as $field) {
            $this->data($field, ['dec', $step]);
        }
        return $this;
    }

    public function getObj(): object
    {
        $this->options['obj'] = true;
        return $this;
    }

    public function getSql(): object
    {
        $this->options['sql'] = true;
        return $this;
    }

    public function table($table): object
    {
        if (is_string($table) && !str_contains($table, ')')) {
            if (strpos($table, ',')) {
                $tables = explode(',', $table);
                $table = [];
                foreach ($tables as $item) {
                    [$item, $alias] = explode(' ', trim($item));
                    if ($alias) {
                        $this->alias([$item => $alias]);
                        $table[$item] = $alias;
                    } else {
                        $table[] = $item;
                    }
                }
            } elseif (strpos($table, ' ')) {
                [$table, $alias] = explode(' ', trim($table));
                $table = [$table => $alias];
                $this->alias($table);
            }
        }
        $this->options['table'] = $table;
        return $this;
    }

    public function alias($alias): object
    {
        if (is_array($alias)) {
            $this->options['alias'] = $alias;
        } else {
            $table = is_array($this->options['table']) ? key($this->options['table']) : $this->options['table'];
            if (!$table) {
                $table = $this->table;
            }
            $this->options['alias'][$table] = $alias;
        }
        return $this;
    }

    public function field($field = '', bool $isExcept = false): object
    {
        if (empty($field)) {
            return $this;
        }
        if (is_string($field)) {
            $field = array_map('trim', explode(',', $field));
        }
        if ($isExcept) {
            $oldFields = $this->options['field'] ?? $this->getFieldList();
            $field = array_diff(array_values($oldFields), $field);
        } else {
            if (isset($this->options['field'])) {
                $field = array_merge($this->options['field'], $field);
            }
        }
        $this->options['field'] = array_unique($field);
        return $this;
    }

    public function limit($offset, ?int $length = null): object
    {
        if (is_string($offset) && strpos($offset, ',')) {
            [$offset, $length] = explode(',', $offset);
        }
        $this->options['limit'] = intval($offset) . ($length ? ',' . intval($length) : '');
        return $this;
    }

    public function page(int $page, int $length): object
    {
        $page = $page > 0 ? ($page - 1) : 0;
        return $this->limit($page * $length, $length);
    }

    public function order($field, string $order = ''): object
    {
        if (!empty($field)) {
            if (is_string($field)) {
                if (strpos($field, ',')) {
                    $field = array_map('trim', explode(',', $field));
                } else {
                    $field = empty($order) ? $field : [$field => $order];
                }
            }
            $this->options['order'] ??= [];
            if (is_array($field)) {
                $this->options['order'] = array_merge($this->options['order'], $field);
            } else {
                $this->options['order'][] = $field;
            }
        }
        return $this;
    }

    public function where($field, $op = null, $condition = null, ?string $logic = null): object
    {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                if (!is_numeric($k)) {
                    if (!is_array($v)) {
                        $this->options['where'][] = [$k, $v];
                    } else {
                        array_unshift($v, $k);
                        $this->options['where'][] = $v;
                    }
                } else {
                    $this->options['where'][] = $v;
                }
            }
        } else {
            $this->options['where'][] = func_get_args();
        }
        return $this;
    }

    public function join($join, $condition = null, string $type = 'INNER'): object
    {
        if (empty($condition)) {
            foreach ($join as $value) {
                if (is_array($value) && 2 <= count($value)) {
                    $this->join($value[0], $value[1], $value[2] ?? $type);
                }
            }
        } else {
            $table = $this->getJoinTable($join);
            $this->options['join'][] = [$table, strtoupper($type), $condition];
        }
        return $this;
    }

    protected function getJoinTable($join, &$alias = null)
    {
        if (is_array($join)) {
            $table = $join;
            $alias = array_shift($join);
        } else {
            $join = trim($join);
            if (str_contains($join, '(')) {
                $table = $join;
            } else {
                if (strpos($join, ' ')) {
                    list($table, $alias) = explode(' ', $join);
                } else {
                    $table = $join;
                    if (!str_contains($join, '.') && !str_starts_with($join, '__')) {
                        $alias = $join;
                    }
                }
            }
            if (isset($alias) && $table != $alias) {
                $table = [$table => $alias];
            }
        }
        return $table;
    }

    public function union($union, bool $all = false): object
    {
        $this->options['union']['type'] = $all ? 'UNION ALL' : 'UNION';
        if (is_array($union)) {
            $this->options['union'] = array_merge($this->options['union'], $union);
        } else {
            $this->options['union'][] = $union;
        }
        return $this;
    }

    public function group(string $group): object
    {
        $this->options['group'] = $group;
        return $this;
    }

    public function having(string $having): object
    {
        $this->options['having'] = $having;
        return $this;
    }

    public function using($using): object
    {
        $this->options['using'] = $using;
        return $this;
    }

    public function extra(string $extra): object
    {
        $this->options['extra'] = $extra;
        return $this;
    }

    public function duplicate($duplicate): object
    {
        $this->options['duplicate'] = $duplicate;
        return $this;
    }

    public function lock($lock = false): object
    {
        $this->options['lock'] = $lock;
        return $this;
    }

    public function distinct($distinct = false): object
    {
        $this->options['distinct'] = $distinct;
        return $this;
    }

    public function force(string $force): object
    {
        $this->options['force'] = $force;
        return $this;
    }

    public function comment(string $comment): object
    {
        $this->options['comment'] = $comment;
        return $this;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->objData[$offset] = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->objData[$offset] ?? null;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return isset($this->objData[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        if (isset($this->objData[$offset])) unset($this->objData[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->objData);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->objData);
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        return next($this->objData);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->objData);
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return current($this->objData);
    }
}