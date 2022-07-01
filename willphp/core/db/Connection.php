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
/**
 * 数据库连接器
 */
class Connection {
	protected $link = null; //PDO链接
	protected $queryResult = null; //查询结果集
	protected $sql = ''; //Sql查询语句
	protected $bind = []; //绑定参数
	protected $error = ''; //错误信息
	protected $numRows = 0; //返回记录数或影响记录数
	protected $config = []; //数据库配置
	/**
	 * 构造方法
	 * @param array $config 数据库配置
	 */
	public function __construct(array $config = []) {		
		$this->config = !empty($config)? $config : Config::get('database.default', []);		
		$this->connect();
	}
	/**
	 * 析构方法
	 */
	public function __destruct() {
		if ($this->queryResult) {
			$this->free();
		}
		$this->link = null;
	}
	/**
	 * 释放查询结果集
	 * @return Connection   自身实例
	 */
	public function free() {
		$this->queryResult = null;
		return $this;
	}
	/**
	 * 获取dsn配置
	 * @return string   dsn配置
	 */
	public function getDsn(array $config = []) {
		$dsn = '';
		if (in_array($config['db_type'], ['pdo', 'mysql', 'mysqli'])) {
			$dsn = 'mysql:dbname='.$config['db_name'].';host='.$config['db_host'];
			if (isset($config['db_port'])) {
				$dsn .= ';port='.$config['db_port'];
			}
			if (isset($config['db_charset'])) {
				$dsn .= ';charset='.$config['db_charset'];
			}
		}
		return $dsn;
	}
	/**
	 * 链接DB
	 * @param  array  $config 配置信息
	 */
	public function connect(array $config = []) {		
		if (!empty($config) && is_array($config)) {
			$this->config = array_merge($this->config, $config);
		}
		if (!isset($this->config['dsn'])) {
			$this->config['dsn'] = $this->getDsn($this->config);
		}
		if (!isset($this->config['pdo_params'])) {
			$this->config['pdo_params'] = [];
		}
		try{
			$this->link = new \PDO($this->config['dsn'], $this->config['db_user'], $this->config['db_pwd'], $this->config['pdo_params']);
			if (in_array($this->config['db_type'], ['pdo', 'mysql', 'mysqli'])) {
				$this->execute("SET sql_mode = ''");
			}
		} catch(\PDOException $e){
			throw new \Exception('ERROR: '.$e->getMessage());
		}		
	}
	/**
	 * 获取DB链接
	 * @return mixed    数据库链接
	 */
	public function getLink() {
		if (is_null($this->link)) {
			$this->connect();
		}		
		return $this->link;
	}
	/**
	 * 获取数据库配置
	 * @param string $name 配置名称
	 * @return mixed
	 */
	public function getConfig($name = '') {
		if (empty($name)) {
			return $this->config;
		}
		return isset($this->config[$name]) ? $this->config[$name] : '';
	}	
	/**
	 * 执行命令语句
	 * @param  string $sql  SQL语句
	 * @param  array  $bind 绑定的值
	 * @return integer 影响行数
	 */
	public function execute($sql, array $bind = []) {
		$this->sql = $sql;
		if (!empty($bind)) {
			$this->bind = $bind;
		}
		if (!empty($this->queryResult) && $this->queryResult->queryString != $sql) {
			$this->free();
		}
		if (empty($this->queryResult)) {
			$this->queryResult = $this->getLink()->prepare($sql);
		}
		$procedure = in_array(strtolower(substr(trim($sql), 0, 4)), ['call', 'exec']); //是否为存储过程调用
		if ($procedure) {
			$this->bindParam($bind);
		} else {
			$this->bindValue($bind);
		}
		try {	
			$this->queryResult->execute();
			$this->numRows = $this->queryResult->rowCount();
			return $this->numRows;			
		} catch(\Exception $e){
			$error = $this->queryResult->errorInfo();
			throw new \Exception($sql.';['.var_export($bind, true).']'.implode(';', $error));
		}		
	}	
	/**
	 * 执行查询语句
	 * @param  string  $sql  SQL语句
	 * @param  array   $bind 绑定的值
	 * @param  boolean $pdo  是否返回PDO对象
	 * @return mixed   查询结果集
	 */
	public function query($sql, array $bind = [], $pdo = false) {
		$this->sql = $sql;
		if (!empty($bind)) {
			$this->bind = $bind;
		}
		if (!empty($this->queryResult)) {
			$this->free();
		}
		if (empty($this->queryResult)) {
			$this->queryResult = $this->getLink()->prepare($sql);
		}
		$procedure = in_array(strtolower(substr(trim($sql), 0, 4)), ['call', 'exec']); //是否为存储过程调用
		if ($procedure) {
			$this->bindParam($bind);
		} else {
			$this->bindValue($bind);
		}
		try {				
			$this->queryResult->execute();
			return $this->getResult($pdo, $procedure);
		}  catch(\Exception $e){
			$error = $this->queryResult->errorInfo();
			throw new \Exception($sql.';['.var_export($bind, true).']'.implode(';', $error));
		}			
	}
	/**
	 * 获得数据集数组
	 * @param bool   $pdo 是否返回queryResult
	 * @param bool   $procedure 是否存储过程
	 * @return queryResult|array 数据集
	 */
	protected function getResult($pdo = false, $procedure = false) {
		if ($pdo) {
			return $this->queryResult;
		}
		if ($procedure) {
			return $this->procedure();
		}	
		$result = $this->queryResult->fetchAll(\PDO::FETCH_ASSOC);
		$this->numRows = count($result);		
		return $result? $result : [];
	}
	/**
	 * 获得存储过程数据集
	 * @return array 存储过程数据集
	 */
	protected function procedure() {
		$item = [];
		do {
			$result = $this->getResult();
			if ($result) {
				$item[] = $result;
			}
		} while ($this->queryResult->nextRowset());
		$this->numRows = count($item);
		return $item;
	}
	/**
	 * 存储过程的输入输出参数绑定
	 * @param array $bind 要绑定的参数列表
	 * @return void
	 */
	protected function bindParam(array $bind) {
		foreach ($bind as $key => $val) {
			$param = is_numeric($key) ? $key + 1 : ':'.$key;
			if (is_array($val)) {
				array_unshift($val, $param);
				$result = call_user_func_array([$this->queryResult, 'bindParam'], $val);
			} else {
				$result = $this->queryResult->bindValue($param, $val);
			}
			if (!$result) {
				$param = array_shift($val);
				throw new \Exception('Bind param error: '.$param);
			}
		}
	}
	/**
	 * 参数绑定
	 * 支持 ['name'=>'value','id'=>123] 对应命名占位符
	 * 或者 ['value',123] 对应问号占位符
	 * @param array $bind 要绑定的参数列表
	 * @return void
	 */
	protected function bindValue(array $bind = []) {
		foreach ($bind as $key => $val) {			
			$param = is_numeric($key) ? $key + 1 : ':'.$key;
			if (is_array($val)) {
				if (\PDO::PARAM_INT == $val[1] && '' === $val[0]) {
					$val[0] = 0;
				}
				$result = $this->queryResult->bindValue($param, $val[0], $val[1]);
			} else {
				$result = $this->queryResult->bindValue($param, $val);
			}
			if (!$result) {
				throw new \Exception('Bind value error: '.$param);			
			}
		}
	}
	/**
	 * PDO安全过滤
	 * @param  string $value 需要过滤的值
	 * @return string 过滤后的值
	 */
	public function quote($value) {
		return $this->getLink()->quote($value);
	}
	/**
	 * 根据参数绑定组装最终的SQL语句 
	 * @param string    $sql  带参数绑定的sql语句
	 * @param array     $bind 参数绑定列表
	 * @return string   拼装后的sql语句
	 */
	public function getRealSql($sql, array $bind = []) {
		if (is_array($sql)) {
			$sql = implode(';', (array) $sql);
		}		
		foreach ($bind as $key => $val) {
			$value = is_array($val) ? $val[0] : $val;
			$type  = is_array($val) ? $val[1] : \PDO::PARAM_STR;
			if (\PDO::PARAM_STR == $type) {
				$value = $this->quote($value);
			} elseif (\PDO::PARAM_INT == $type) {
				$value = (float) $value;
			}
			if (is_numeric($key)) {
				$sql = substr_replace($sql, $value, strpos($sql, '?'), 1);
			} else {
				$sql = str_replace(
						[':'.$key.')', ':'.$key.',', ':'.$key.' ', ':'.$key.PHP_EOL],
						[$value. ')', $value.',', $value.' ', $value.PHP_EOL],
						$sql.' '
				);
			}
		}
		return rtrim($sql);
	}
	/**
	 * 获取返回记录数或影响记录数
	 * @return integer
	 */
	public function getNumRows() {
		return $this->numRows;
	}
	/**
	 * 获取最后插入记录的ID
	 * @param  string|null $pk 自增序列名
	 * @return mixed    最后新增的ID
	 */
	public function getInsertId($pk = null) {
		return $this->getLink()->lastInsertId($pk);
	}
	/**
	 * 获取最近一次查询的sql语句
	 * @return  string 最后执行的sql语句
	 */
	public function getLastSql() {
		return $this->getRealSql($this->sql, (array) $this->bind);
	}
	/**
	 * 执行事务处理
	 * @param \Closure $closure
	 * @return $this
	 */
	public function transaction(\Closure $closure) {
		try {
			$this->startTrans();		
			call_user_func($closure);
			$this->commit();
		} catch (\Exception $e) {
			$this->rollback();
		}		
		return $this;
	}
	/**
	 * 开启事务
	 * @return $this
	 */
	public function startTrans() {
		$this->getLink()->beginTransaction();		
		return $this;
	}
	/**
	 * 回滚事务
	 * @return $this
	 */
	public function rollback() {
		$this->getLink()->rollback();		
		return $this;
	}
	/**
	 * 提交事务
	 * @return $this
	 */
	public function commit() {
		$this->getLink()->commit();		
		return $this;
	}
}