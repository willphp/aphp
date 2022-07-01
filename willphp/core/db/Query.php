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
use willphp\core\Config;
use willphp\core\Cache;
use willphp\core\Page;
use willphp\core\Middleware;
use willphp\core\Filter;
/**
 * 查询构造器
 */
class Query implements \ArrayAccess, \Iterator {
	protected $connection = null; //DB链接实例
	protected $builder = null; //SQL构造实例
	protected $table; //当前表名
	protected $model; //当前模型对象
	protected $options = []; //查询参数
	protected $bind = []; //参数绑定标识位
	protected $objData = []; //对象数据
	/**
	 * 构造方法
	 * @param array $config 数据库配置
	 */
	public function __construct($config = [], $table = '') {
		$this->connection = new Connection($config);
		$this->table = $table;
		$this->builder = new Builder($this->connection, $this);		
	}
	/**
	 * 获取当前表名
	 * @return string
	 */
	public function getTable() {
		$table = $this->getOptions('table');
		return $table? $table : $this->table;
	}	
	/**
	 * 获取字段列表
	 * @return array
	 */
	public function getFieldList($table) {	
		if (!$table) {
			$table = $this->getTable();
		}					
		$name = 'field.'.$table.'_field';
		$cache = Cache::get($name);
		if (!$cache) {
			$cache = $this->getAllFields($table);
			if ($cache) {
				Cache::set($name, $cache);
			}
		}		
		return $cache? $cache : [];		
	}
	/**
	 * 获取主键
	 * @param string $table
	 * @return string
	 */
	public function getPk($table = '') {
		$fields = $this->getFieldList($table);
		return isset($fields['pri'])? $fields['pri'] : 'id';
	}
	/**
	 * 移除表中不存在的字段
	 * @param array $data
	 * @param string $table
	 * @return array
	 */
	public function filterTableField(array $data, $table = '') {
		$new = [];		
		$fields = $this->getFieldList($table);
		if (is_array($data)) {
			foreach ((array)$data as $name => $value) {
				if (in_array($name, $fields)) {
					$new[$name] = $value;
				}
			}
		}		
		return $new;
	}
	/**
	 * 指定模型
	 * @param Model $model 模型对象实例
	 * @return Query 当前实例自身
	 */
	public function setModel($model) {
		$this->model = $model;
	}	
	/**
	 * 获取当前的模型对象
	 * @return Model|null 当前操作模型对象
	 */
	public function getModel() {
		return $this->model;
	}
	/**
	 * 获取当前的查询参数
	 * @param  string $name 参数名称
	 * @return mixed 查询参数
	 */
	public function getOptions($name = '') {
		if ('' === $name) {
			return $this->options;
		} 
		return isset($this->options[$name]) ? $this->options[$name] : null;		
	}
	/**
	 * 执行查询 返回数据集
	 * @param string      $sql  sql指令
	 * @param array       $bind 参数绑定
	 * @param bool|string $obj	指定返回的数据集对象
	 * @return mixed 数据集
	 */
	public function query($sql, $bind = [], $obj = false) {			
		$realsql = $this->connection->getRealSql($sql, $bind);				
		Middleware::web('database_query', $realsql);	
		return $this->connection->query($sql, $bind, $obj);
	}
	/**
	 * 执行语句
	 * @param string $sql  sql指令
	 * @param array  $bind 参数绑定
	 * @return mixed 受影响行数
	 */
	public function execute($sql, $bind = []) {
		$realsql = $this->connection->getRealSql($sql, $bind);
		Middleware::web('database_execute', $realsql);	
		return $this->connection->execute($sql, $bind);
	}
	/**
	 * 分析表达式（用于查询或者写入操作）
	 * @return array
	 */
	public function parseExpress() {
		$options = $this->options;
		if (empty($options['table'])) {
			$options['table'] = $this->table;
		}
		if (empty($options['table'])) {
			throw new \Exception('The query table is not set!');
		}
		if (!isset($options['field'])) {
			$options['field'] = '*';
		}
		if (!isset($options['data'])) {
			$options['data'] = [];
		}
		if (!isset($options['where'])) {
			$options['where'] = [];
		}
		foreach (['lock', 'distinct'] as $name) {
			if (!isset($options[$name])) {
				$options[$name] = false;
			}
		}
		$params = ['join', 'union', 'group', 'having', 'limit', 'order', 'force', 'comment', 'extra', 'using', 'duplicate'];
		foreach ($params as $name) {
			if (!isset($options[$name])) {
				$options[$name] = '';
			}
		}		
		$this->options = [];
		return $options;
	}	
	/**
	 * 检测参数是否已经绑定
	 * @param string $key 参数名
	 * @return boolean
	 */
	public function isBind($key) {
		return isset($this->bind[$key]);
	}
	/**
	 * 参数绑定
	 * @param mixed   $key   参数名
	 * @param mixed   $value 绑定变量值
	 * @param integer $type  绑定类型
	 * @return Query    当前实例自身
	 */
	public function bind($key, $value = false, $type = \PDO::PARAM_STR) {
		if (is_array($key)) {
			$this->bind = array_merge((array) $this->bind, $key);
		} else {
			$this->bind[$key] = [$value, $type];
		}
		return $this;
	}
	/**
	 * 获取绑定的参数 并清空
	 * @return array
	 */
	public function getBind() {
		$bind = $this->bind;
		$this->bind = [];
		return $bind;
	}
	//================后置方法(返回查询结果)====================
	/**
	 * 查询数据
	 * @return array 查询结果集
	 */
	public function select() {
		$options = $this->parseExpress();
		$sql = $this->builder->select($options);
		$bind = $this->getBind();
		if (isset($options['sql']) && $options['sql']) {
			return $this->connection->getRealSql($sql, $bind);
		}
		$obj = (isset($options['obj']) && $options['obj']);
		$result = $this->query($sql, $bind, $obj);
		return $result;
	}	
	/**
	 * 查找单条记录
	 * @param integer $id  	查找的id主键
	 * @return array 查询结果集
	 */
	public function find($id = 0) {		
		if ($id > 0) {			
			$pk = $this->getPk();
			$this->where($pk, $id);			
		}
		$result = $this->limit(1)->select();
		if ($result instanceof \PDOStatement || is_string($result)) {
			return $result;
		}	
		return isset($result[0])? $result[0] : [];
	}
	/**
	 * 分页查询
	 * @param int $row     每页显示数量
	 * @param int $pageNum 页面数量
	 * @return 当前对像
	 */
	public function paginate($row, $pageNum = 8) {
		$options = $this->options;	
		$count = $this->count();		
		Page::set(['pageSize'=>$row, 'pageNum'=>$pageNum])->make($count);
		$this->options = $options;
		$this->options['limit'] = Page::getLimit();				
		$res = $this->select();	
		if (!is_array($res)) {
			return $res;
		}
		$this->objData = ($res)? Filter::output($res) : [];	
		return $this;
	}
	/**
	 * 显示分页
	 * @return mixed
	 */
	public function links() {
		return Page::single();
	}
	public function getAttr($attr = '') {
		return Page::getAttr($attr);
	}
	/**
	 * 获取对象数据
	 * @return mixed
	 */
	public function toArray() {
		return $this->objData;
	}
	/**
	 * 查找字段
	 * @param string|array $field 查找的字段
	 * @return string|array 查询字段结果
	 */
	public function getField($field) {
		if (!is_array($field)) {
			if(strpos($field, ',') === false) {
				$result = $this->field($field)->find();
				return isset($result[$field])? $result[$field] : '';
			} 
			$field = explode(',', $field);			
		}
		$field_count = count($field); //字段数		
		$result = $this->field($field)->select();
		if ($result instanceof \PDOStatement || is_string($result)) {
			return $result;
		}
		$data = [];
		foreach ($result as $row) {
			$key = $row[$field[0]];
			if ($field_count == 2) {				
				$data[$key] = $row[$field[1]];
			} else {
				$data[$key] = $row;
			}
		}
		return $data;		
	}
	/**
	 * 获取SQL结果
	 * @param string $sql 查询语句
	 * @return array  结果集
	 */
	public function getResult($sql) {
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
	/**
	 * 更新查询
	 * @param  array  $data 更新的数据
	 * @return integer  受影响行数
	 */
	public function update(array $data = []) {
		$options = $this->parseExpress();
		if (empty($options['where'])) {			
			throw new \Exception('The update operation query condition cannot be empty!');
		}
		$data = array_merge($options['data'], $data);		
		$data = $this->filterTableField($data);		
		$sql = $this->builder->update($data, $options);
		if (!$sql) {
			throw new \Exception('The generated query statement is empty!');
		}
		$bind = $this->getBind();
		if (isset($options['sql']) && $options['sql']) {
			return $this->connection->getRealSql($sql, $bind);
		}
		$result = $this->execute($sql, $bind);
		return $result;
	}	
	/**
	 * 设置字段
	 * @param string $field 设置的字段
	 * @param string $value 设置的值
	 * @return integer  受影响行数
	 */
	public function setField($field, $value) {
		return $this->data($field, $value)->update();
	}
	/**
	 * 字段自增
	 * @param string  $field 字段名
	 * @param integer $step  步长
	 * @return integer 影响行数
	 */
	public function setInc($field, $step = 1) {
		return $this->inc($field, $step)->update();
	}	
	/**
	 * 字段自减
	 * @param string  $field 字段名
	 * @param integer $step  步长
	 * @return integer 影响行数
	 */
	public function setDec($field, $step = 1) {
		return $this->dec($field, $step)->update();
	}
	/**
	 * 删除操作
	 * @param integer|array $ids  删除的id主键
	 * @param  string  $key       自增主键名，默认为id
	 * @return integer  影响行数
	 */
	public function delete($ids = []) {		
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
			throw new \Exception('The delete operation query condition cannot be empty!');
		}
		$sql = $this->builder->delete($options);
		$bind = $this->getBind();
		if (isset($options['sql']) && $options['sql']) {
			return $this->connection->getRealSql($sql, $bind);
		}
		$result = $this->execute($sql, $bind);
		return $result;
	}		
	/**
	 * 插入操作, 默认返回影响行数
	 * @param  array   $data         插入数据
	 * @param  boolean $getLastInsID 返回自增主键ID
     * @param  boolean $replace      是否replace
	 * @return integer 影响行数或自增ID
	 */
	public function insert(array $data = [], $getLastInsID = false, $replace = false) {
		$options = $this->parseExpress();		
		$data = array_merge($options['data'], $data);				
		$data = $this->filterTableField($data, $options['table']);		
		$sql = $this->builder->insert($data, $options, $replace);		
		$bind = $this->getBind();
		if (isset($options['sql']) && $options['sql']) {
			return $this->connection->getRealSql($sql, $bind);
		}
		$result = (false === $sql) ? false : $this->execute($sql, $bind);
		if ($result && $getLastInsID) {			
			$pk = $this->getPk($options['table']);
			return $this->getInsertId($pk);
		}
		return $result;
	}	
	/**
	 * replace插入, 默认返回影响行数
	 * @param  array   $data         插入数据
	 * @param  boolean $getLastInsID 返回自增主键ID
	 * @return integer 影响行数或自增ID
	 */
	public function replace(array $data = [], $getLastInsID = false) {		
		return $this->insert($data, $getLastInsID, true);
	}	
	/**
	 * 插入返回自增ID
	 * @param  array   $data         插入数据
	 * @return integer 影响行数或自增ID
	 */
	public function insertGetId(array $data = []) {
		return $this->insert($data, true, false);
	}
	/**
	 * 批量插入数据
	 * @param  array   $data    数据集
	 * @param  boolean $replace 是否replace
	 * @return integer  影响行数
	 */
	public function insertAll(array $data = [], $replace = false) {
		$options = $this->parseExpress();
		if (!is_array($data)) {		
			return false;
		}
		$sql = $this->builder->insertAll($data, $options, $replace);
		$bind = $this->getBind();
		if (isset($options['sql']) && $options['sql']) {
			return $this->connection->getRealSql($sql, $bind);
		}
		$result = $this->execute($sql, $bind);
		return $result;
	}
	/**
	 * 获取表的所有字段
	 * @param string $table 表名(不含前缀)
	 * @return array 所有字段数组 键名为pri是自增主键
	 */
	public function getAllFields($table) {
		$prefix = $this->connection->getConfig('db_prefix'); 	
		$sql = 'show columns from '.$prefix.$table;
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
	/**
	 * 统计查询
	 * @param string $field 字段名
	 * @param string $type 统计类型
	 * @return integer|string 结果集
	 */
	public function total($field, $type = 'count') {
		$alias = 'willphp_'.strtolower($type);
		$type = strtoupper($type);
		$total_type = ['COUNT', 'SUM', 'MIN', 'MAX', 'AVG'];
		if (in_array($type, $total_type)) {			
			$options = [];
			if (isset($this->options['table'])) {
				$options['table'] = $this->options['table'];
			}
			if (isset($this->options['where'])) {
				$options['where'] = $this->options['where'];
			}
			if (isset($this->options['sql'])) {
				$options['sql'] = true;
			}
			$this->options = $options;			
			$res = $this->field($type.'('.$field.') AS '.$alias)->find();
			if ($res instanceof \PDOStatement || is_string($res)) {
				return $res;
			}
			return isset($res[$alias]) ? $res[$alias] : 0;
		}
		return false;
	}	
	/**
	 * COUNT查询
	 * @param string $field 字段名
	 * @return integer|string 结果集
	 */
	public function count($field = '*') {
		return $this->total($field, 'count');
	}
	/**
	 * SUM查询
	 * @param string	$field 字段名
 	 * @return integer|string 结果集
	 */
	public function sum($field) {
		return $this->total($field, 'sum');
	}
	/**
	 * MAX查询
	 * @param string $field 字段名
 	 * @return integer|string 结果集
	 */
	public function max($field)	{
		return $this->total($field, 'max');
	}
	/**
	 * MIN查询
	 * @param string $field 字段名
	 * @return integer|string 结果集
	 */
	public function min($field)	{
		return $this->total($field, 'min');
	}
	/**
	 * AVG查询
	 * @param string $field 字段名
	 * @return integer|string 结果集
	 */
	public function avg($field)	{
		return $this->total($field, 'avg');
	}
	//==================前置方法(设置查询参数)====================	
	/**
	 * 获取PDO结果集
	 * @return Query  当前实例自身
	 */
	public function getObj() {
		$this->options['obj'] = true;
		return $this;
	}
	/**
	 * 获取SQL语句，不执行操作
	 * @return Query  当前实例自身
	 */
	public function getSql() {
		$this->options['sql'] = true;
		return $this;
	}
	/**
	 * 设置数据
	 * @param mixed $field 字段名或者数据
	 * @param mixed $value 字段值
	 * @return Query 当前实例自身
	 */
	public function data($field, $value = null)	{
		if (is_array($field)) {
			$this->options['data'] = isset($this->options['data']) ? array_merge($this->options['data'], $field) : $field;
		} else {
			$this->options['data'][$field] = $value;
		}
		return $this;
	}
	/**
	 * 字段值增长
	 * @param string|array $field 字段名
	 * @param integer      $step  增长值
	 * @return Query    当前实例自身
	 */
	public function inc($field, $step = 1) {
		$fields = is_string($field) ? explode(',', $field) : $field;
		foreach ($fields as $field) {
			$this->data($field, ['inc', $step]);
		}
		return $this;
	}
	/**
	 * 字段值减少
	 * @param string|array $field 字段名
	 * @param integer      $step  增长值
	 * @return Query    当前实例自身
	 */
	public function dec($field, $step = 1) {
		$fields = is_string($field) ? explode(',', $field) : $field;
		foreach ($fields as $field) {
			$this->data($field, ['dec', $step]);
		}
		return $this;
	}
	/**
	 * 设置表名(不含表前缀)
	 * @param  string $table 表名(不含表前缀)
	 * @return Query 当前实例自身
	 */
	public function table($table) {
		if (is_string($table)) {
			if (strpos($table, ')')) {
				//子查询
			} elseif (strpos($table, ',')) {
				//多表
				$tables = explode(',', $table);
				$table  = [];
				foreach ($tables as $item) {
					list($item, $alias) = explode(' ', trim($item));
					if ($alias) {
						$this->alias([$item => $alias]);
						$table[$item] = $alias;
					} else {
						$table[] = $item;
					}
				}
			} elseif (strpos($table, ' ')) {
				list($table, $alias) = explode(' ', $table);				
				$table = [$table => $alias];
				$this->alias($table);
			}
		}		
		$this->options['table'] = $table;
		return $this;
	}
	/**
	 * 设置数据表别名 (不含表前缀)
	 * @param string|array $alias 数据表别名
	 * @return Query 当前实例自身
	 */
	public function alias($alias) {
		if (is_array($alias)) {
			foreach ($alias as $k => $v) {			
				$this->options['alias'][$k] = $v;
			}
		} else {
			$table = is_array($this->options['table']) ? key($this->options['table']) : $this->options['table'];
			if (!$table) {
				$table = $this->table;
			}
			$this->options['alias'][$table] = $alias;
		}		
		return $this;
	}
	/**
	 * 设置查询字段
	 * @param  string|array $field 查询字段
	 * @return Query    当前实例自身
	 */
	public function field($field = '') {
		if (empty($field)) {
			return $this;
		}
		if (is_string($field)) {
			$field = array_map('trim', explode(',', $field));
		}
		if (isset($this->options['field'])) {
			$field = array_merge((array) $this->options['field'], $field);
		}
		$this->options['field'] = array_unique($field);
		return $this;
	}
	/**
	 * 设置查询数量
	 * @param mixed $offset 起始位置
	 * @param mixed $length 查询数量
	 * @return Query    当前实例自身
	 */
	public function limit($offset, $length = null) {
		if (is_null($length) && strpos($offset, ',')) {
			list($offset, $length) = explode(',', $offset);
		}
		$this->options['limit'] = intval($offset).($length ? ','.intval($length) : '');		
		return $this;
	}
	/**
	 * 设置分页查询
	 * @param integer $page     当前页数，从1开始
	 * @param integer $length   每页记录条数
	 * @return Query  当前实例自身
	 */
	public function page($page, $length) {
		$page = intval($page);
		$page = $page > 0 ? ($page - 1) : 0;
		$length = intval($length);
		return $this->limit($page * $length, $length);
	}
	/**
	 * 设置排序order('id ASC,ctime DESC') | order('id','desc') | order(['id'=>'desc','ctime'=>'desc'])
	 * @param string|array $field 排序字段
	 * @param string       $order 排序
	 * @return Query    当前实例自身
	 */
	public function order($field, $order = '') {
		if (!empty($field)) {
			if (is_string($field)) {
				if (strpos($field, ',')) {
					$field = array_map('trim', explode(',', $field));
				} else {
					$field = empty($order) ? $field : [$field => $order];
				}
			}			
			if (!isset($this->options['order'])) {
				$this->options['order'] = [];
			}
			if (is_array($field)) {
				$this->options['order'] = array_merge($this->options['order'], $field);
			} else {
				$this->options['order'][] = $field;
			}
		}		
		return $this;
	}		
	/**
	 * 查询 union
	 * @param mixed   $union
	 * @param boolean $all
	 * @return Query    当前实例自身
	 */
	public function union($union, $all = false)	{
		$this->options['union']['type'] = $all ? 'UNION ALL' : 'UNION';
		if (is_array($union)) {
			$this->options['union'] = array_merge($this->options['union'], $union);
		} else {
			$this->options['union'][] = $union;
		}
		return $this;
	}
	/**
	 * 设置join查询SQL组装
	 * @param mixed  $join      关联的表名
	 * @param mixed  $condition 条件
	 * @param string $type      JOIN类型
	 * @return Query    当前实例自身
	 */
	public function join($join, $condition = null, $type = 'INNER') {
		if (empty($condition)) {
			foreach ($join as $key => $value) {
				if (is_array($value) && 2 <= count($value)) {
					$this->join($value[0], $value[1], isset($value[2]) ? $value[2] : $type);
				}
			}
		} else {
			$table = $this->getJoinTable($join);
			$this->options['join'][] = [$table, strtoupper($type), $condition];
		}
		return $this;
	}
	/**
	 * 获取Join表名及别名 支持
	 * ['prefix_table或者子查询'=>'alias'] 'prefix_table alias' 'table alias'
	 * @param array|string $join
	 * @return array|string
	 */
	protected function getJoinTable($join, &$alias = null) {
		if (is_array($join)) {
			$table = $join;
			$alias = array_shift($join);
		} else {
			$join = trim($join);
			if (false !== strpos($join, '(')) {
				$table = $join;
			} else {
				if (strpos($join, ' ')) {
					list($table, $alias) = explode(' ', $join);
				} else {
					$table = $join;
					if (false === strpos($join, '.') && 0 !== strpos($join, '__')) {
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
	/**
	 * 设置查询条件
	 * @param mixed $field     查询字段
	 * @param mixed $op        查询表达式
	 * @param mixed $condition 查询条件
     * @param string $logic    查询逻辑 and(默认) or
	 * @return Query    当前实例自身
	 */
	public function where($field, $op = null, $condition = null, $logic = null) {				
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

	/**
	 * 设置group查询
	 * @param string $group GROUP
	 * @return Query    当前实例自身
	 */
	public function group($group) {
		$this->options['group'] = $group;
		return $this;
	}	
	/**
	 * 设置having查询
	 * @param string $having having
	 * @return Query    当前实例自身
	 */
	public function having($having) {
		$this->options['having'] = $having;
		return $this;
	}
	/**
	 * USING支持 用于多表删除
	 * @param string|array $using USING
	 * @return Query    当前实例自身
	 */
	public function using($using) {
		$this->options['using'] = $using;
		return $this;
	}	
	/**
	 * 设置查询的额外参数
	 * @param string $extra 额外信息
	 * @return Query    当前实例自身
	 */
	public function extra($extra) {
		$this->options['extra'] = $extra;
		return $this;
	}	
	/**
	 * 设置DUPLICATE
	 * @param array|string $duplicate DUPLICATE信息
	 * @return Query    当前实例自身
	 */
	public function duplicate($duplicate) {
		$this->options['duplicate'] = $duplicate;
		return $this;
	}
	/**
	 * 查询lock
	 * @param boolean|string $lock 是否lock
	 * @return Query    当前实例自身
	 */
	public function lock($lock = false) {
		$this->options['lock'] = $lock;
		return $this;
	}
	/**
	 * distinct查询
	 * @param string|boolean $distinct 是否唯一
	 * @return Query    当前实例自身
	 */
	public function distinct($distinct = false)	{
		$this->options['distinct'] = $distinct;
		return $this;
	}
	/**
	 * 指定强制索引
	 * @param string $force 索引名称
	 * @return Query    当前实例自身
	 */
	public function force($force) {
		$this->options['force'] = $force;		
		return $this;
	}
	/**
	 * 查询注释
	 * @param string $comment 注释
	 * @return Query    当前实例自身
	 */
	public function comment($comment) {
		$this->options['comment'] = $comment;		
		return $this;
	}
	/**
	 * 魔术方法
	 * @param string $method 方法
	 * @param array $params 参数
	 * @return Connection方法结果 
	 */
	public function __call($method, $params) {	
		if (substr($method, 0, 5) == 'getBy') {
			$field = preg_replace('/.[A-Z]/', '_\1', substr($method, 5));
			$field = strtolower($field);			
			return $this->where($field, current($params))->find();
		}
		return call_user_func_array([$this->connection, $method], $params);
	}
	public function offsetSet($key, $value) {
		$this->objData[$key] = $value;
	}	
	public function offsetGet($key)	{
		return isset($this->objData[$key]) ? $this->objData[$key] : null;
	}	
	public function offsetExists($key)	{
		return isset($this->objData[$key]);
	}	
	public function offsetUnset($key)	{
		if (isset($this->objData[$key])) {
			unset($this->objData[$key]);
		}
	}	
	public function rewind() {
		reset($this->objData);
	}	
	public function current() {
		return current($this->objData);
	}	
	public function next() {
		return next($this->objData);
	}	
	public function key() {
		return key($this->objData);
	}	
	public function valid()	{
		return current($this->objData);
	}
}
