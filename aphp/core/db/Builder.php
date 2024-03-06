<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core\db;

use aphp\core\Single;
use aphp\core\Tool;

class Builder
{
    use Single;

    protected Connection $connection;
    protected Query $query;
    protected array $params = [];

    private function __construct(Connection $connection, Query $query)
    {
        $this->connection = $connection;
        $this->query = $query;
    }

    public function select(array $options): string
    {
        $this->params = $options;
        $sql = str_replace(
            ['%TABLE%', '%DISTINCT%', '%EXTRA%', '%FIELD%', '%JOIN%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%', '%UNION%', '%LOCK%', '%COMMENT%', '%FORCE%'],
            [
                $this->parseTable(),
                $this->parseDistinct(),
                $this->parseExtra(),
                $this->parseField(),
                $this->parseJoin(),
                $this->parseWhere(),
                $this->parseGroup(),
                $this->parseHaving(),
                $this->parseOrder(),
                $this->parseLimit(),
                $this->parseUnion(),
                $this->parseLock(),
                $this->parseComment(),
                $this->parseForce(),
            ],
            'SELECT%DISTINCT%%EXTRA% %FIELD% FROM %TABLE%%FORCE%%JOIN%%WHERE%%GROUP%%HAVING%%UNION%%ORDER%%LIMIT%%LOCK%%COMMENT%'
        );
        $this->params = [];
        return trim($sql);
    }

    public function delete(array $options): string
    {
        $this->params = $options;
        $sql = str_replace(
            ['%TABLE%', '%EXTRA%', '%USING%', '%JOIN%', '%WHERE%', '%ORDER%', '%LIMIT%', '%LOCK%', '%COMMENT%'],
            [
                $this->parseTable(),
                $this->parseExtra(),
                $this->parseUsing(),
                $this->parseJoin(),
                $this->parseWhere(),
                $this->parseOrder(),
                $this->parseLimit(),
                $this->parseLock(),
                $this->parseComment(),
            ],
            'DELETE%EXTRA% FROM %TABLE%%USING%%JOIN%%WHERE%%ORDER%%LIMIT% %LOCK%%COMMENT%'
        );
        $this->params = [];
        return trim($sql);
    }

    public function insert(array $data, array $options = [], bool $replace = false): string
    {
        $this->params = $options;
        $data = $this->parseData($data);
        if (empty($data)) {
            return '';
        }
        $fields = array_keys($data);
        $values = array_values($data);
        $sql = str_replace(
            ['%INSERT%', '%TABLE%', '%EXTRA%', '%FIELD%', '%DATA%', '%DUPLICATE%', '%COMMENT%'],
            [
                $replace ? 'REPLACE' : 'INSERT',
                $this->parseTable(),
                $this->parseExtra(),
                implode(', ', $fields),
                implode(', ', $values),
                $this->parseDuplicate(),
                $this->parseComment(),
            ],
            '%INSERT%%EXTRA% INTO %TABLE% (%FIELD%) VALUES (%DATA%) %DUPLICATE%%COMMENT%'
        );
        $this->params = [];
        return trim($sql);
    }

    public function update(array $data, array $options): string
    {
        $this->params = $options;
        $data = $this->parseData($data);
        if (empty($data)) {
            return '';
        }
        $set = [];
        foreach ($data as $key => $val) {
            $set[] = $key . '=' . $val;
        }
        $sql = str_replace(
            ['%TABLE%', '%EXTRA%', '%SET%', '%JOIN%', '%WHERE%', '%ORDER%', '%LIMIT%', '%LOCK%', '%COMMENT%'],
            [
                $this->parseTable(),
                $this->parseExtra(),
                implode(',', $set),
                $this->parseJoin(),
                $this->parseWhere(),
                $this->parseOrder(),
                $this->parseLimit(),
                $this->parseLock(),
                $this->parseComment(),
            ],
            'UPDATE%EXTRA% %TABLE% %JOIN% SET %SET% %WHERE% %ORDER%%LIMIT% %LOCK%%COMMENT%'
        );
        $this->params = [];
        return trim($sql);
    }

    public function insertAll(array $dataSet, array $options = [], bool $replace = false): string
    {
        $this->params = $options;
        $fields = $options['field'];
        $insertFields = $values = [];
        foreach ($dataSet as $data) {
            if (is_array($fields)) {
                $data = Tool::arr_key_filter($data, $fields, true);
            }
            if (empty($insertFields)) {
                $insertFields = array_keys($data);
            }
            $data = array_map(fn($v) => is_scalar($v) ? $this->parseValue($v) : 'null', $data);
            $values[] = '(' . implode(',', array_values($data)) . ')';
        }
        $sql = str_replace(
            ['%INSERT%', '%TABLE%', '%EXTRA%', '%FIELD%', '%DATA%', '%DUPLICATE%', '%COMMENT%'],
            [
                $replace ? 'REPLACE' : 'INSERT',
                $this->parseTable(),
                $this->parseExtra(),
                implode(', ', $insertFields),
                implode(', ', $values),
                $this->parseDuplicate(),
                $this->parseComment(),
            ],
            '%INSERT%%EXTRA% INTO %TABLE% (%FIELD%) VALUES %DATA% %DUPLICATE%%COMMENT%'
        );
        $this->params = [];
        return trim($sql);
    }

    protected function parseValue($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        if (is_string($value)) {
            $value = str_starts_with($value, ':') && $this->query->isBind(substr($value, 1)) ? $value : $this->connection->quote($value);
        } elseif (is_array($value)) {
            $value = array_map([$this, 'parseValue'], $value);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_null($value)) {
            $value = 'null';
        }
        return $value;
    }

    protected function parseData(array $data): array
    {
        if (empty($data)) {
            return [];
        }
        $exp = ['inc' => '+', 'dec' => '-'];
        $result = [];
        foreach ($data as $key => $val) {
            $item = $this->parseKey($key, true);
            if (is_null($val)) {
                $result[$item] = 'NULL';
            } elseif (is_array($val) && !empty($val)) {
                $k = strtolower($val[0]);
                if (isset($exp[$k])) {
                    $result[$item] = $item . $exp[$k] . floatval($val[1]);
                }
            } elseif (is_scalar($val)) {
                $val = strval($val);
                if (str_starts_with($val, ':') && $this->query->isBind(substr($val, 1))) {
                    $result[$item] = $val;
                } else {
                    $key = str_replace('.', '_', $key);
                    $this->query->bind('data__' . $key, $val);
                    $result[$item] = ':data__' . $key;
                }
            }
        }
        return $result;
    }

    protected function parseWhere(): string
    {
        $whereStr = $this->buildWhere($this->params['where']);
        return !empty($whereStr) ? ' WHERE ' . $whereStr : '';
    }

    protected function buildWhere(array $where): string
    {
        if (empty($where)) {
            return '';
        }
        $express = $logic = [];
        $i = 1;
        foreach ($where as $wh) {
            $arg_count = count($wh); //参数数量
            $tmp = false;
            if ($arg_count == 1) {
                $tmp = $wh[0];
            }
            if ($arg_count == 2) {
                $tmp = $this->getExpress($wh[0], $wh[1]);
            }
            if ($arg_count >= 3) {
                $tmp = $this->getExpress($wh[0], $wh[2], $wh[1]);
            }
            if ($tmp) {
                $express[$i] = $tmp;
                $logic[$i] = isset($wh[3]) ? strtoupper($wh[3]) : 'AND';
                $i++;
            }
        }
        return $this->linkExpress($express, $logic);
    }

    public function getExpress(string $field, $condition, string $op = '=')
    {
        if (is_array($condition) && $op == '=') {
            $this->query->bind($condition);
            return $field;
        }
        $op = strtoupper($op);
        if (in_array($op, ['=', '<>', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE'])) {
            $express = $this->parseValue($condition);
            if (is_scalar($express)) {
                return $this->parseKey($field) . ' ' . $op . ' ' . $express;
            }
            return false;
        }
        if (in_array($op, ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'])) {
            if (!is_array($condition)) {
                $condition = explode(',', $condition);
            }
            $express = $this->parseValue($condition);
            if ($op == 'BETWEEN' || $op == 'NOT BETWEEN') {
                return '(' . $this->parseKey($field) . ' ' . $op . ' ' . implode(' AND ', $express) . ')';
            }
            return $this->parseKey($field) . ' ' . $op . ' (' . implode(',', $express) . ')';
        }
        return false;
    }

    protected function linkExpress(array $express, array $logic): string
    {
        $where = '';
        $count = count($express);
        $logic[$count] = 'AND';
        for ($i = 1; $i <= $count; $i++) {
            $left = $right = $link = '';
            if ($logic[$i] != 'AND' && ($i == 1 || ($i < $count && $logic[$i - 1] == 'AND'))) {
                $left = '(';
            }
            if ($i > 1 && $logic[$i - 1] != 'AND' && $logic[$i] == 'AND') {
                $right = ')';
            }
            if ($i > 1 && $i <= $count) {
                $link = ' ' . $logic[$i - 1] . ' ';
            }
            $where .= $link . $left . $express[$i] . $right;
        }
        return $where;
    }

    protected function parseTable($tables = ''): string
    {
        $tables = empty($tables) ? $this->params['table'] : $tables;
        $prefix = $this->connection->getConfig('table_prefix');
        $item = [];
        foreach ((array)$tables as $key => $val) {
            $table = !is_numeric($key) ? $key : $val;
            if (!is_numeric($key)) {
                $alias = $val;
            } else {
                $alias = $this->params['alias'][$val] ?? '';
            }
            $item[] = $this->parseKey($prefix . $table) . (!empty($alias) ? ' ' . $this->parseKey($alias) : '');
        }
        return implode(',', $item);
    }

    protected function parseDistinct(): string
    {
        return !empty($this->params['distinct']) ? ' DISTINCT ' : '';
    }

    protected function parseExtra(): string
    {
        $extra = $this->params['extra'];
        return preg_match('/^\w+$/i', $extra) ? ' ' . strtoupper($extra) : '';
    }

    protected function parseField(): string
    {
        $fields = $this->params['field'];
        $fieldsStr = '*';
        if (is_array($fields)) {
            $array = [];
            foreach ($fields as $key => $field) {
                $array[] = is_numeric($key) ? $this->parseKey($field) : $this->parseKey($key) . ' AS ' . $this->parseKey($field, true);
            }
            $fieldsStr = implode(',', $array);
        }
        return $fieldsStr;
    }

    protected function parseJoin(): string
    {
        $join = $this->params['join'];
        $joinStr = '';
        if (!empty($join)) {
            foreach ($join as $item) {
                [$table, $type, $on] = $item;
                $condition = [];
                foreach ((array)$on as $val) {
                    if (strpos($val, '=')) {
                        [$val1, $val2] = explode('=', $val, 2);
                        $condition[] = $this->parseKey($val1) . '=' . $this->parseKey($val2);
                    } else {
                        $condition[] = $val;
                    }
                }
                $table = $this->parseTable($table);
                $joinStr .= ' ' . $type . ' JOIN ' . $table . ' ON ' . implode(' AND ', $condition);
            }
        }
        return $joinStr;
    }

    protected function parseOrder(): string
    {
        $order = $this->params['order'];
        $array = [];
        foreach ($order as $key => $val) {
            if ('[rand]' == $val) {
                $array[] = 'rand()';
            } else {
                $sort = 'ASC';
                if (is_numeric($key)) {
                    if (str_contains($val, ' ')) {
                        [$key, $sort] = explode(' ', $val);
                    } else {
                        $key = $val;
                    }
                } else {
                    $sort = $val;
                }
                $sort = strtoupper($sort);
                $sort = in_array($sort, ['ASC', 'DESC'], true) ? ' ' . $sort : '';
                $array[] = $this->parseKey($key, true) . $sort;
            }
        }
        $order = implode(',', $array);
        return !empty($order) ? ' ORDER BY ' . $order : '';
    }

    protected function parseLimit(): string
    {
        $limit = $this->params['limit'];
        return (!empty($limit) && !str_contains($limit, '(')) ? ' LIMIT ' . $limit . ' ' : '';
    }

    protected function parseLock(): string
    {
        $lock = $this->params['lock'];
        if (is_bool($lock)) {
            return $lock ? ' FOR UPDATE ' : '';
        } elseif (is_string($lock)) {
            return ' ' . trim($lock) . ' ';
        }
        return '';
    }

    protected function parseUnion(): string
    {
        $union = $this->params['union'];
        if (empty($union)) {
            return '';
        }
        $type = $union['type'];
        unset($union['type']);
        $sql = [];
        foreach ($union as $u) {
            if (is_string($u)) {
                $sql[] = $type . ' ( ' . $u . ' )';
            }
        }
        return ' ' . implode(' ', $sql);
    }

    protected function parseComment(): string
    {
        $comment = $this->params['comment'];
        if (str_contains($comment, '*/')) {
            $comment = strstr($comment, '*/', true);
        }
        return !empty($comment) ? ' /* ' . $comment . ' */' : '';
    }

    protected function parseForce(): string
    {
        $index = $this->params['force'];
        if (empty($index)) {
            return '';
        }
        return sprintf(" FORCE INDEX ( %s ) ", is_array($index) ? implode(',', $index) : $index);
    }

    protected function parseGroup(): string
    {
        return !empty($this->params['group']) ? ' GROUP BY ' . $this->parseKey($this->params['group']) : '';
    }

    protected function parseHaving(): string
    {
        return !empty($this->params['having']) ? ' HAVING ' . $this->params['having'] : '';
    }

    protected function parseUsing(): string
    {
        return !empty($this->params['using']) ? ' USING ' . $this->parseTable($this->params['using']) . ' ' : '';
    }

    protected function parseDuplicate(): string
    {
        $duplicate = $this->params['duplicate'];
        if (empty($duplicate)) {
            return '';
        }
        $updates = [];
        if (is_string($duplicate)) {
            $updates[] = $duplicate;
        } else {
            foreach ($duplicate as $key => $val) {
                if (is_numeric($key)) {
                    $val = $this->parseKey($val);
                    $updates[] = $val . ' = VALUES(' . $val . ')';
                } else {
                    $updates[] = $this->parseKey($key) . ' = ' . $this->connection->quote($val);
                }
            }
        }
        return ' ON DUPLICATE KEY UPDATE ' . implode(' , ', $updates) . ' ';
    }

    protected function parseKey($val, bool $strict = false): string
    {
        if (is_numeric($val)) return $val;
        $val = trim($val);
        $table = '';
        if (str_contains($val, '.') && !preg_match('/[,\'\"()`\s]/', $val)) {
            [$table, $val] = explode('.', $val, 2);
            $table = $this->params['alias'][$table] ?? $table;
            if (str_contains($table, '.')) {
                $table = str_replace('.', '`.`', $table);
            }
            $table = '`' . $table . '`.';
        }
        if ('*' != $val && ($strict || !preg_match('/[,\'\"*()`.\s]/', $val))) {
            $val = '`' . $val . '`';
        }
        return $table . $val;
    }

}