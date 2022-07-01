<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace willphp\core\model;
use willphp\core\Config;
use willphp\core\Validate;
use willphp\core\validate\ValidateRule;
/**
 * 自动验证
 */
trait Verify {	
	protected $isBatch = false; //是否批量验证
	protected $validate = []; //验证规则	
	protected $errors = []; //错误信息	
	/**
	 * 设置错误
	 * @param array|string $error
	 */
	public function setError($error) {
		$error = is_array($error)? $error : [$error];
		$this->errors = array_merge($this->errors, $error);
	}
	/**
	 * 获取错误
	 * @return array
	 */
	public function getError() {
		return $this->errors;
	}	
	/**
	 * 自动验证
	 * @return bool
	 */	
	final protected function autoValidate() {
		$this->errors = [];
		if (empty($this->original)) {
			throw new \Exception('No data for operation');
		}
		if (empty($this->validate)) {
			return true;
		}		
		$validateRule = new ValidateRule();
		$data = &$this->original;	
		$regex = Config::get('regex', []); //正则配置
		foreach ($this->validate as $validate) {
			$field = $validate[0]; //字段
			if (!isset($this->errors[$field])) {
				$this->errors[$field] = '';
			}
			$at = isset($validate[3]) ? $validate[3] : AT_SET; //验证条件
			if ($at == AT_NOT_NULL && empty($data[$field])) {
				continue;
			}
			if ($at == AT_NULL && !empty($data[$field])) {
				continue;
			}
			if ($at == AT_SET && !isset($data[$field])) {
				continue;
			}
			if ($at == AT_NOT_SET && isset($data[$field])) {
				continue;
			}
			$action = isset($validate[4]) ? $validate[4] : IN_BOTH; //验证时机	
			
			if ($action != $this->action() && $action != IN_BOTH) {
				continue;
			}				
			$rule = explode('|', $validate[1]); //验证规则
			$info = explode('|', $validate[2]); //提示信息
			$value = isset($data[$field]) ? $data[$field] : '';			
			foreach ($rule as $k=>$fn) {
				$msg = isset($info[$k])? $info[$k] : $info[0]; //提示
				list($method, $params) = explode(':', $fn); //方法与参数
				if (method_exists($this, $method)) {
					if ($this->$method($value, $field, $params, $data) !== true) {
						$this->errors[$field] .= '|'.$msg;
					}
				} elseif (method_exists($validateRule, $method)) {
					if ($validateRule->$method($value, $field, $params, $data) != true) {
						$this->errors[$field] .= '|'.$msg;
					}
				} elseif (substr($method, 0, 1) == '/') {
					//正则
					if (!preg_match($method, $value)) {
						$this->errors[$field] .= '|'.$msg;
					}
				} elseif (array_key_exists($method, $regex)) {
					//正则
					if (!preg_match($regex[$method], $value)) {
						$this->errors[$field] .= '|'.$msg;
					}
				} elseif (in_array($method, ['url','email','ip','float','int','boolean'])) {
					//filter_var
					if ($validateRule->filter($value, $field, $method, $data) !== true) {
						$this->errors[$field] .= '|'.$msg;
					}
				} elseif (function_exists($method)) {
					//函数
					if ($method($value) != true) {
						$this->errors[$field] .= '|'.$msg;
					}
				} else {
					$this->errors[$field] .= '|'.$fn.' 验证方法不存在';
				}
				$this->errors[$field] = trim($this->errors[$field], '|');
				if (!$this->isBatch && !empty($this->errors[$field])) break;
			}
			if (!$this->isBatch && !empty($this->errors[$field])) break;
		}
		$this->errors = array_filter($this->errors);
		Validate::respond($this->errors);		
		return $this->errors ? false : true;
	}
	/**
	 * 唯一前置
	 */
	protected function _before_unique($data) {}
	/**
	 * 自动验证字段值唯一(自动验证使用)
	 * @param $value 字段值
	 * @param $field 字段名
	 * @param $param 参数
	 * @param $data  提交数据
	 * @return bool 验证状态
	 */
	final protected function unique($value, $field, $params, $data) {
		$this->_before_unique($data);
		$db = $this->db->where($field, $value);
		if ($this->action() == IN_UPDATE) {
			$db->where($this->pk, '<>', $this->data[$this->pk]);
		}
		if (empty($value) || !$db->find()) {
			return true;
		}
	}
}