<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace willphp\core\db;
/**
 * Sql生成器
 */
class Builder {
	protected $connection = null;
	protected $query = null;
	protected $params = []; //options参数	
	/**
	 * 构造方法
	 * @param Connection $connection 数据库连接
	 * @param Query $query 查询构造器
	 */
	public function __construct(Connection $connection, Query $query) {
		$this->connection = $connection;
		$this->query = $query;
	}
	/**
	 * 生成select SQL
	 * @param array     $options 表达式
	 * @return string
	 */
	public function select(array $options) {		
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
		return $sql;
	}	
	/**
	 * 生成delete SQL
	 * @param array     $options 表达式
	 * @return string
	 */
	public function delete(array $options) {
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
		return $sql;
	}	
	/**
	 * 生成insert SQL
	 * @param array     $data 数据集
	 * @param array     $options 表达式
	 * @param bool      $replace 是否replace
	 * @return string
	 */
	public function insert(array $data, array $options = [], $replace = false) {
		$this->params = $options;
		$data = $this->parseData($data);
		if (empty($data)) {
			return false;
		}	
		$fields = array_keys($data);
		$values = array_values($data);
		$sql = str_replace(
				['%INSERT%', '%TABLE%', '%EXTRA%', '%FIELD%', '%DATA%', '%DUPLICATE%', '%COMMENT%'],
				[
					$replace ? 'REPLACE' : 'INSERT',
					$this->parseTable(),
					$this->parseExtra(),
					implode(' , ', $fields),
					implode(' , ', $values),
					$this->parseDuplicate(),
					$this->parseComment(),
				],
				'%INSERT%%EXTRA% INTO %TABLE% (%FIELD%) VALUES (%DATA%) %DUPLICATE%%COMMENT%'
		);
		$this->params = [];
		return $sql;
	}
	/**
	 * 生成update SQL
	 * @param array     $data 更新的数据
	 * @param array     $options 表达式
	 * @return string
	 */
	public function update(array $data, array $options) {
		$this->params = $options;
		$data  = $this->parseData($data);
		if (empty($data)) {
			return '';
		}
		foreach ($data as $key => $val) {
			$set[] = $key.'='.$val;
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
		return $sql;
	}
	/**
	 * 生成insertall SQL
	 * @param array     $dataSet 数据集
	 * @param array     $options 表达式
	 * @param bool      $replace 是否replace
	 * @return string
	 */
	public function insertAll($dataSet, $options = [], $replace = false) {
		$this->params = $options;
		$fields = $options['field'];			
		foreach ($dataSet as $data) {
			foreach ($data as $key => $val) {
				if (is_array($fields) && !in_array($key, $fields)) {	
					unset($data[$key]);
				} else if (is_null($val)) {
					$data[$key] = 'NULL';
				} elseif (is_scalar($val)) {
					$data[$key] = $this->parseValue($val, $key);
				} else {
					unset($data[$key]);
				}
			}
			$value = array_values($data);
			$values[] = '( '.implode(',', $value).' )';			
			if (!isset($insertFields)) {
				$insertFields = array_keys($data);
			}
		}
		$sql = str_replace(
				['%INSERT%', '%TABLE%', '%EXTRA%', '%FIELD%', '%DATA%', '%DUPLICATE%', '%COMMENT%'],
				[
						$replace ? 'REPLACE' : 'INSERT',
						$this->parseTable(),
						$this->parseExtra(),
						implode(' , ', $insertFields),
						implode(' , ', $values),
						$this->parseDuplicate(),
						$this->parseComment(),
				],  
				'%INSERT%%EXTRA% INTO %TABLE% (%FIELD%) VALUES %DATA% %DUPLICATE%%COMMENT%'
		);		
		$this->params = [];
		return $sql;
	}
	/**
	 * 数据处理
	 * @param array     $data 数据
	 * @return array
	 */
	protected function parseData($data) {
		if (empty($data)) {
			return [];
		}		
		$fields = $this->params['field'];
		$result = [];
		foreach ($data as $key => $val) {
			$item = $this->parseKey($key, true);			
			if (is_null($val)) {
				$result[$item] = 'NULL';
			} elseif (is_array($val) && !empty($val)) {
				switch (strtolower($val[0])) {
					case 'inc':
						$result[$item] = $item.'+'.floatval($val[1]);
						break;
					case 'dec':
						$result[$item] = $item.'-'.floatval($val[1]);
						break;
				}
			} elseif (is_scalar($val)) {				
				if (0 === strpos($val, ':') && $this->query->isBind(substr($val, 1))) {
					$result[$item] = $val;
				} else {
					$key = str_replace('.', '_', $key);	
					$this->query->bind('data__'.$key, $val);
					$result[$item] = ':data__'.$key;
				}
			}
		}
		return $result;		
	}
	/**
	 * 表处理
	 * @param string|array $tables 表名
	 * @return string
	 */
	protected function parseTable($tables = '') {
		$tables = empty($tables)? $this->params['table'] : $tables;
		$prefix = $this->connection->getConfig('db_prefix'); //表前缀		
		$item = [];			
		foreach ((array) $tables as $key => $table) {			
			if (!is_numeric($key)) {				
				$item[] = $this->parseKey($prefix.$key).' '.(isset($this->params['alias'][$table]) ? $this->parseKey($this->params['alias'][$table]) : $this->parseKey($table));
			} else {				
				if (isset($this->params['alias'][$table])) {
					$item[] = $this->parseKey($prefix.$table).' '.$this->parseKey($this->params['alias'][$table]);
				} else {
					$item[] = $this->parseKey($prefix.$table);
				}
			}
		}
		return implode(',', $item);
	}	
	/**
	 * 字段处理
	 * @return string
	 */
	protected function parseField() {
		$fields = $this->params['field'];
		if ('*' == $fields || empty($fields)) {
			$fieldsStr = '*';
		} elseif (is_array($fields)) {
			//支持 '字段'=>'别名' 这样的字段别名定义
			$array = [];
			foreach ($fields as $key => $field) {
				if (!is_numeric($key)) {
					$array[] = $this->parseKey($key) . ' AS ' . $this->parseKey($field, true);
				} else {
					$array[] = $this->parseKey($field);
				}
			}
			$fieldsStr = implode(',', $array);
		}
		return $fieldsStr;
	}	
	/**
	 * join处理
	 * @return string
	 */
	protected function parseJoin() {
		$join = $this->params['join'];
		$joinStr = '';
		if (!empty($join)) {
			foreach ($join as $item) {
				list($table, $type, $on) = $item;
				$condition = [];
				foreach ((array) $on as $val) {
					if (strpos($val, '=')) {
						list($val1, $val2) = explode('=', $val, 2);
						$condition[] = $this->parseKey($val1) . '=' . $this->parseKey($val2);
					} else {
						$condition[] = $val;
					}
				}				
				$table = $this->parseTable($table);
				$joinStr .= ' '.$type.' JOIN '.$table.' ON '.implode(' AND ', $condition);
			}
		}
		return $joinStr;
	}	
	/**
	 * where处理
	 * @return string
	 */
	protected function parseWhere(){
		$where = $this->params['where'];
		$whereStr = $this->buildWhere($where);		
		return empty($whereStr) ? '' : ' WHERE '.$whereStr;
	}
	/**
	 * 生成where
	 * @return string
	 */
	protected function buildWhere($where) {
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
				$logic[$i] = isset($wh[3])? strtoupper($wh[3]) : 'AND';
				$i ++;
			}
		}		
		return $this->linkExpress($express, $logic);
	}
	/**
	 * 连接查询表达式
	 * @param array $express	where表达式
     * @param array $logic    	查询逻辑 and or
	 * @return string			sql的where条件
	 */
	protected function linkExpress(array $express, array $logic) {
		$where = '';
		$count = count($express);
		$logic[$count] = 'AND';
		for ($i=1;$i<=$count;$i++) {
			$left = $right = $link = '';
			if ($logic[$i] != 'AND') {
				if ($i == 1) {
					$left = '(';
				} elseif ($i < $count && $logic[$i-1] == 'AND') {
					$left = '(';
				}				
			}
			if ($i > 1 && $logic[$i-1] != 'AND' && $logic[$i] == 'AND') {
				$right = ')';
			}
			if ($i > 1 && $i <= $count) {
				$link = ' '.$logic[$i-1].' ';
			}
			$where .= $link.$left.$express[$i].$right;
		}
		return $where;
	}	
	/**
	 * 获取分析后的查询表达式
	 * @param mixed $field		字段
	 * @param mixed $condition 	查询条件
	 * @param mixed $op        	查询表达式
     * @return string|false		sql表达式语句
	 */
	public function getExpress($field, $condition, $op = '=') {
		$op = strtoupper($op);
		$express = false;
		if (in_array($op, ['=','<>','>','>=','<','<='])) {
			$express = $this->parseValue($condition);
			if (is_scalar($express)) {
				return $this->parseKey($field).$op.$express;
			}
		}
		if ($op == 'EXP') {
			return $this->parseKey($field).' '.$condition;
		}
		if ($op == 'IN' || $op == 'NOT IN') {
			if (is_array($condition)) {
				$express = implode(',', $condition);
			} elseif (strpos($condition, ',')) {
				$express = $condition;
			}
			if ($express) {
				return $this->parseKey($field).' '.$op.' ('.$express.')';
			} 
		}
		if ($op == 'BETWEEN' || $op == 'NOT BETWEEN') {
			if (is_array($condition)) {
				$express = implode(' AND ', $condition);
			} elseif (strpos($condition, ',')) {
				$express = implode(' AND ', explode(',', $condition));
			}
			if ($express) {
				return '('.$this->parseKey($field).' '.$op.' '.$express.')';
			} 
		}
		if ($op == 'LIKE' || $op == 'NOT LIKE') {
			return $this->parseKey($field).' '.$op.' \''.$condition.'\'';
		}
		return $express;
	}	
	/**
	 * value分析
	 * @param mixed     $value
	 * @return string|array
	 */
	protected function parseValue($value, $field = '') {
		if (is_string($value)) {
			$value = strpos($value, ':') === 0 && $this->query->isBind(substr($value, 1)) ? $value : $this->connection->quote($value);
		} elseif (is_array($value)) {
			$value = array_map([$this, 'parseValue'], $value);
		} elseif (is_bool($value)) {
			$value = $value ? '1' : '0';
		} elseif (is_null($value)) {
			$value = 'null';
		}
		return $value;
	}	
	/**
	 * group处理
	 * @return string
	 */
	protected function parseGroup(){
		$group = $this->params['group'];
		return !empty($group) ? ' GROUP BY '.$this->parseKey($group) : '';
	}
	/**
	 * having处理
	 * @return string
	 */
	protected function parseHaving(){
		$having = $this->params['having'];
		return !empty($having) ? ' HAVING '.$having : '';
	}
	/**
	 * order处理
	 * @return string
	 */
	protected function parseOrder(){
		$order = $this->params['order'];
		if (empty($order)) {
			return '';
		}		
		$array = [];
		foreach ($order as $key => $val) {
			if ('[rand]' == $val) {
				$array[] = 'rand()';
			} else {
				if (is_numeric($key)) {
					list($key, $sort) = explode(' ', strpos($val, ' ') ? $val : $val.' ');
				} else {
					$sort = $val;
				}
				$sort = strtoupper($sort);
				$sort = in_array($sort, ['ASC', 'DESC'], true) ? ' ' . $sort : '';
				$array[] = $this->parseKey($key, true).$sort;
			}
		}
		$order = implode(',', $array);		
		return !empty($order) ? ' ORDER BY '.$order : '';		
	}
	/**
	 * limit处理
	 * @return string
	 */
	protected function parseLimit(){
		$limit = $this->params['limit'];
		return (!empty($limit) && false === strpos($limit, '(')) ? ' LIMIT ' . $limit . ' ' : '';
	}
	/**
	 * lock处理
	 * @return string
	 */
	protected function parseLock(){
		$lock = $this->params['lock'];
		if (is_bool($lock)) {
			return $lock ? ' FOR UPDATE ' : '';
		} elseif (is_string($lock)) {
			return ' '.trim($lock).' ';
		}
	}	
	/**
	 * distinct处理
	 * @return string
	 */
	protected function parseDistinct() {
		$distinct = $this->params['distinct'];
		return !empty($distinct) ? ' DISTINCT ' : '';
	}
	/**
	 * extra处理
	 * @return string
	 */
	protected function parseExtra() {
		$extra = $this->params['extra'];
		return preg_match('/^[\w]+$/i', $extra) ? ' ' . strtoupper($extra) : '';
	}
	/**
	 * union处理
	 * @return string
	 */
	protected function parseUnion() {
		$union = $this->params['union'];
		if (empty($union)) {
			return '';
		}
		$type = $union['type'];
		unset($union['type']);
		foreach ($union as $u) {
			if (is_string($u)) {
				$sql[] = $type.' ( '.$u.' )';
			}
		}
		return ' '.implode(' ', $sql);
	}
	/**
	 * comment处理
	 * @return string
	 */
	protected function parseComment() {
		$comment = $this->params['comment'];
		if (false !== strpos($comment, '*/')) {
			$comment = strstr($comment, '*/', true);
		}
		return !empty($comment) ? ' /* '.$comment.' */' : '';
	}
	/**
	 * force处理
	 * @return string
	 */
	protected function parseForce() {
		$index = $this->params['force'];
		if (empty($index)) {
			return '';
		}
		return sprintf(" FORCE INDEX ( %s ) ", is_array($index) ? implode(',', $index) : $index);
	}
	/**
	 * duplicate处理
	 * @return string
	 */
	protected function parseDuplicate() {
		$duplicate = $this->params['duplicate'];
		if ('' == $duplicate) {
			return '';
		}
		$updates = [];
		if (is_string($duplicate)) {
			$updates[] = $duplicate;
		} else {
			foreach ($duplicate as $key => $val) {
				if (is_numeric($key)) {
					$val = $this->parseKey($val);
					$updates[] = $val.' = VALUES('.$val.')';
				} else {
					$updates[] = $this->parseKey($key).' = '.$this->connection->quote($val);
				}
			}
		}		
		return ' ON DUPLICATE KEY UPDATE '.implode(' , ', $updates).' ';
	}
	/**
	 * using处理
	 * @return string
	 */
	protected function parseUsing() {
		$using = $this->params['using'];
		return !empty($using) ? ' USING '.$this->parseTable($using).' ' : '';			
	}
	/**
	 * key处理
	 * @return string
	 */
	protected function parseKey($key, $strict = false) {
		if (is_numeric($key)) {
			return $key;
		}		
		$key = trim($key);
		if (strpos($key, '.') && !preg_match('/[,\'\"\(\)`\s]/', $key)) {
			list($table, $key) = explode('.', $key, 2);			
			if (isset($this->params['alias'][$table])) {
				$table = $this->params['alias'][$table];
			}
		}		
		if ('*' != $key && ($strict || !preg_match('/[,\'\"\*\(\)`.\s]/', $key))) {
			$key = '`'.$key.'`';
		}
		if (isset($table)) {
			if (strpos($table, '.')) {
				$table = str_replace('.', '`.`', $table);
			}
			$key = '`'.$table.'`.'. $key;
		}
		return $key;
	}
}