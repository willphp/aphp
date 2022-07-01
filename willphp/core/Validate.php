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
use willphp\core\validate\ValidateRule;
class Validate {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new ValidateBuilder();
		}
		return self::$link;
	}
	public function __call($method, $params) {
		return call_user_func_array([self::single(), $method], $params);
	}
	public static function __callStatic($name, $arguments) {
		return call_user_func_array([self::single(), $name], $arguments);
	}
}
class ValidateBuilder extends ValidateRule { 
	protected $extend = []; //扩展规则
	protected $errors = []; //错误信息
	/**
	 * 设置扩展规则
	 * @param $name
	 * @param $callback
	 */
	public function extend($name, $callback) {
		if ($callback instanceof \Closure) {
			$this->extend[$name] = $callback;
		}
	}
	/**
	 * 是否验证失败
	 * @return bool
	 */
	public function isFail() {
		return !empty($this->errors);
	}
	/**
	 * 获取错误
	 * @return array
	 */
	public function getError() {
		return $this->errors;
	}
	/**
	 * 数据验证
	 * @param array $validates 验证规则
	 * @param array $data 数据
	 * @param bool	$isBatch 是否批量验证
	 * @return $this
	 */
	public function make(array $validates, array $data = [], $isBatch = false) {
		$this->errors = [];
		if (empty($data)) {
			$data = $_POST;
		}		
		$regex = Config::get('regex', []); //正则配置
		foreach ($validates as $validate) {
			$field = $validate[0]; //字段
			if (!isset($this->errors[$field])) {
				$this->errors[$field] = '';
			}
			$at = isset($validate[3]) ? $validate[3] : AT_MUST; //验证条件
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
			$value = isset($data[$field]) ? $data[$field] : ''; //字段值
			$rule = $validate[1]; //验证规则
			if ($rule instanceof \Closure) {	
				//直接函数 
				if ($rule($value) !== true) {
					$this->errors[$field] = $validate[2];
				}
			} else {
				$rule = explode('|', $rule); //规则列表
				$info = explode('|', $validate[2]); //提示信息
				foreach ($rule as $k=>$action) {
					$msg = isset($info[$k])? $info[$k] : $info[0]; //提示					
					list($method, $params) = explode(':', $action); //方法与参数					
					if (method_exists($this, $method)) {	
						//当前方法
						if ($this->$method($value, $field, $params, $data) !== true) {
							$this->errors[$field] .= '|'.$msg;
						}
					} elseif (isset($this->extend[$method])) {
						//扩展方法
						$callback = $this->extend[$method];
						if ($callback instanceof \Closure) {
							if ($callback($value, $field, $params, $data) !== true) {
								$this->errors[$field] .= '|'.$msg;
							}
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
						if ($this->filter($value, $field, $method, $data) !== true) {
							$this->errors[$field] .= '|'.$msg;
						}
					} elseif (function_exists($method)) {
						//函数
						if (!$method($value)) {
							$this->errors[$field] .= '|'.$msg;
						}
					} else {
						$this->errors[$field] .= '|'.$action.' 验证方法不存在';
					}
					$this->errors[$field] = trim($this->errors[$field], '|');
					if (!$isBatch && !empty($this->errors[$field])) break;
				}
			}
			if (!$isBatch && !empty($this->errors[$field])) break;
		}
		
		$this->errors = array_filter($this->errors);
		return $this->respond($this->errors);
	}
	/**
	 * 错误验证处理
	 * @param array $errors 错误
	 * @return bool
	 */
	public function respond(array $errors) {
		if (!empty($errors)) {
			$dispose = Config::get('app.validate_dispose', 'show');
			if ($dispose == 'redirect' && isset($_SERVER['HTTP_REFERER']) && !IS_AJAX) {				
				header('Location:'.$_SERVER['HTTP_REFERER']);
				die;
			} elseif ($dispose == 'show') {
				App::halt($errors, 'validate');		
			}				
			return false;
		}
		return true;
	}
}