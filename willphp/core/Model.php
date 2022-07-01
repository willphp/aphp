<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace willphp\core;
use willphp\core\model\Verify;
use willphp\core\model\Auto;
use willphp\core\model\Filter;
use willphp\core\Db;
use willphp\core\db\Query;
use willphp\core\Collection;
abstract class Model implements \ArrayAccess, \Iterator {
	use Verify, Auto, Filter;
	protected $table; //表名
	protected $pk = 'id'; //表自增主键
	protected $db; //数据库连接
	protected $dbConfig = []; //数据库配置	
	protected $allowFill = ['*']; //允许填充字段	
	protected $denyFill = []; //禁止填充字段	
	protected $data = []; //模型数据	
	protected $fields = []; //读取字段	
	protected $original = []; //构建数据
	protected $autoTimestamp = 'int'; //自动写入时间戳字段类型(false不自动写入)：int|date|datetime|timestamp 
	protected $createTime = 'ctime'; //创建时间字段
	protected $updateTime = 'uptime'; //更新时间字段
	protected $prefix; //表前缀
	/**
	 * 构造函数
	 */
	public function __construct() {
		if (!$this->table) {
			$this->setTable($this->table);
		}
		$this->db = Db::connect($this->dbConfig, $this->table);
		if (!$this->prefix) {
			$this->prefix = $this->db->getConfig('db_prefix');
		}
	}
	/**
	 * 设置表名
	 * @param $table
	 * @return $this
	 */
	protected function setTable($table) {
		if (empty($table)) {
			$model = basename(str_replace('\\', '/', get_class($this)));			
			$table = name_snake($model);
		}
		$this->table = $table;
		return $this;
	}
	/**
	 * 获取表名
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}
	/**
	 * 获取表前缀
	 * @return string
	 */
	public function getPrefix() {
		return $this->prefix;
	}
	/**
	 * 获取主键
	 * @return mixed
	 */
	public function getPk() {
		return $this->pk;
	}
	/**
	 * 动作类型(新增或更新)
	 * @return int
	 */
	final public function action() {		
		return empty($this->data[$this->pk])? IN_INSERT : IN_UPDATE;
	}
	/**
	 * 获取数据
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}
	/**
	 * 设置data 记录信息属性
	 * @param array $data
	 * @return $this
	 */
	public function setData(array $data) {
		$this->data = array_merge($this->data, $data);
		$this->fields = $this->data;
		$this->getFormatAttribute();		
		return $this;
	}
	/**
	 * 更新widget缓存
	 */
	public function updateWidget() {		
		Cache::clear('widget/'.$this->table);
	}
	/**
	 * 用于读取数据成功时的对字段的处理后返回
	 * @param $field
	 * @return mixed
	 */
	protected function getFormatAttribute()	{
		foreach ($this->fields as $name => $val) {			
			$method = 'get'.name_camel($name).'Attr';
			if (method_exists($this, $method)) {
				$this->fields[$name] = $this->$method($val);
			}
		}		
		return $this->fields;
	}
	/**
	 * 对象数据转为数组
	 * @return array
	 */
	final public function toArray() {
		$data = $this->fields;
		foreach ($data as $k => $v) {
			if (is_object($v) && method_exists($v, 'toArray')) {
				$data[$k] = $v->toArray();
			}
		}		
		return $data;
	}
	/**
	 * 更新模型的时间戳
	 * @return bool
	 */
	final public function touch() {
		if ($this->action() == IN_UPDATE && $this->autoTimestamp && $this->updateTime) { 
			$data = [];
			$data[$this->updateTime] = $this->getFormatTime($this->autoTimestamp);
			return $this->db->where($this->pk, $this->data[$this->pk])->update($data);
		}		
		return false;
	}
	/**
	 * 新增前置
	 */
	protected function _before_insert(array &$data) {}
	/**
	 * 更新前置
	 */
	protected function _before_update(array &$data) {}
	/**
	 * 删除前置
	 */
	protected function _before_delete(array $data) {}
	/**
	 * 新增后置
	 */
	protected function _after_insert(array $data) {}
	/**
	 * 更新后置
	 */
	protected function _after_update(array $old, array $new) {}
	/**
	 * 删除后置
	 */
	protected function _after_delete(array $data) {}
	/**
	 * 更新或添加数据
	 * @param array $data 批量添加的数据
	 * @return bool
	 * @throws \Exception
	 */
	final public function save(array $data = []) {	
		$this->fieldFillCheck($data); //自动填充数据处理	
		//自动验证
		if (!$this->autoValidate()) {
			return false;
		}			
		$this->autoOperation(); //自动完成	
		$this->autoFilter(); //自动过滤	
		$this->formatFields(); //处理时间字段
		if ($this->action() == IN_UPDATE) {
			$this->original = array_merge($this->data, $this->original);			
		}		
		//更新条件检测
		$res = null;			
		switch ($this->action()) {				
			case IN_UPDATE:
				//更新前置		
				$this->_before_update($this->original);				
				if (!$this->original) {
					Validate::respond($this->errors);	
					return false;
				}
				$res = $this->db->where($this->pk, $this->data[$this->pk])->update($this->original);
				if ($res) {
					$old = $this->data;
					$this->setData($this->db->find($this->data[$this->pk]));
					//更新后置				
					$this->_after_update($old, $this->data);
					$this->updateWidget(); //更新widget缓存
				}
				break;
			case IN_INSERT:
				if (isset($this->original[$this->pk])) {
					unset($this->original[$this->pk]);	
				}
				//新增前置			
				$this->_before_insert($this->original);				
				if (!$this->original) {
					Validate::respond($this->error);
					return false;
				}
				$res = $this->db->insertGetId($this->original);
				if ($res) {
					if (is_numeric($res) && $this->pk) {
						$this->setData($this->db->find($res));
						//新增后置						
						$new = array_merge($this->original, $this->data);
						$this->_after_insert($new);	
						$this->updateWidget(); //更新widget缓存
					}
				}
				break;
		}
		$this->original = [];		
		return $res ? $this : false;
	}	
	/**
	 * 批量设置做准备数据
	 * @return $this
	 */
	final private function formatFields() {		
		if ($this->action() == IN_UPDATE) {
			$this->original[$this->pk] = $this->data[$this->pk];			
		}
		//自动填充创建时间和更新时间
		if ($this->autoTimestamp) {
			if ($this->updateTime) {
				$this->original[$this->updateTime] = $this->getFormatTime($this->autoTimestamp);
			}			
			if ($this->action() == IN_INSERT && $this->createTime) {
				$this->original[$this->createTime] = $this->getFormatTime($this->autoTimestamp);
			}
		}		
		return $this;
	}	
	/**
	 * 根据类型获取时间  类型：int|date|datetime|timestamp
	 * @return 格式时间
	 */
	protected function getFormatTime($type = '') {
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
	/**
	 * 自动填充数据处理
	 * @param array $data
	 * @throws \Exception
	 */
	final private function fieldFillCheck(array $data) {
		if (empty($this->allowFill) && empty($this->denyFill)) {
			return;
		}
		//允许填充的数据
		if (!empty($this->allowFill) && $this->allowFill[0] != '*') {
			$data = $this->filterKeys($data, $this->allowFill, 0);
		}
		//禁止填充的数据
		if (!empty($this->denyFill)) {
			if ($this->denyFill[0] == '*') {
				$data = [];
			} else {
				$data = $this->filterKeys($data, $this->denyFill, 1);
			}
		}
		$this->original = array_merge($this->original, $data);
	}
	/**
	 * 根据下标过滤数据元素
	 * @param array $data 原数组数据
	 * @param       $keys 参数的下标
	 * @param int   $type 1 存在在$keys时过滤  0 不在时过滤
	 * @return array
	 */
	public function filterKeys(array $data, $keys, $type = 1) {
		$tmp = $data;
		foreach ($data as $k => $v) {
			if ($type == 1) {				
				if (in_array($k, $keys)) {
					unset($tmp[$k]);
				}
			} else {				
				if (!in_array($k, $keys)) {
					unset($tmp[$k]);
				}
			}
		}		
		return $tmp;
	}
	/**
	 * 删除数据
	 * @return bool
	 */
	final public function destory() {		
		$id = $this->data[$this->pk];
		if (!empty($id)) {
			$data = $this->data;
			//删除前置
			$this->_before_delete($data);
			if ($this->db->delete($id)) {			
				$this->setData([]);	
				//删除后置
				$this->_after_delete($data);
				$this->updateWidget(); //更新widget缓存
				return true;
			}
		}		
		return false;
	}
	/**
	 * 获取模型值
	 * @param $name
	 * @return mixed
	 */
	public function __get($name)	{
		if (isset($this->fields[$name])) {
			return $this->fields[$name];
		}
		if (method_exists($this, $name)) {
			return $this->$name();
		}
	}
	/**
	 * 设置模型数据值
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value) {
		$this->original[$name] = $value;
		$this->data[$name] = $value;
	}
	/**
	 * 魔术方法
	 * @param $method
	 * @param $params
	 * @return mixed
	 */
	public function __call($method, $params) {		
		$before = '_before_'.$method;
		if (method_exists($this, $before)) {
			$this->$before($params);
		}	
		$res = call_user_func_array([$this->db, $method], $params);
		return $this->returnParse($method, $res);
	}	
	protected function returnParse($method, $result) {
		if (!empty($result)) {
			$after = '_after_'.$method;
			if (method_exists($this, $after) && is_array($result)) {
				$this->$after($result);
			}			
			switch (strtolower($method)) {
				case 'find':	
					$result = \willphp\core\Filter::output($result);
					return $this->setData($result);
				case 'paginate':
					$collection = Collection::make([]);
					foreach ($result as $k => $v) {
						$instance = new static();
						$collection[$k] = $instance->setData($v);
					}					
					return $collection;
				default:
					if ($result instanceof Query) {
						return $this;
					}
			}
		}		
		return $result;
	}
	/**
	 * 调用静态方法
	 * @param $method
	 * @param $params
	 * @return mixed
	 */
	public static function __callStatic($method, $params) {
		return call_user_func_array([new static(), $method], $params);
	}
	public function offsetSet($key, $value) {
		$this->original[$key] = $value;
		$this->data[$key] = $value;
		$this->fields[$key] = $value;
	}
	public function offsetGet($key)	{
		return isset($this->fields[$key]) ? $this->fields[$key] : null;
	}
	public function offsetExists($key) {
		return isset($this->data[$key]);
	}
	public function offsetUnset($key) {
		if (isset($this->original[$key])) {
			unset($this->original[$key]);
		}
		if (isset($this->data[$key])) {
			unset($this->data[$key]);
		}
		if (isset($this->fields[$key])) {
			unset($this->fields[$key]);
		}
	}
	function rewind() {
		reset($this->data);
	}
	public function current() {
		return current($this->fields);
	}
	public function next() {
		return next($this->fields);
	}
	public function key() {
		return key($this->fields);
	}
	public function valid()	{
		return current($this->fields);
	}
}