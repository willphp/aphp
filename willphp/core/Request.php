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
class Request {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new RequestBuilder();
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
class RequestBuilder {
	protected $items = []; //请求集合	
	public function __construct() {
		$this->items['GET'] = $_GET;
		$this->items['POST'] = $_POST;
		$this->items['ALL'] = array_merge($_GET, $_POST);
		if (empty($_POST)) {
			$input = file_get_contents('php://input');
			$data = json_decode($input, true);
			if ($data) {
				$this->items['POST'] = $data;
			}
		}
	}
	public function getRequest($name = '', $default = null, $functions = []) {
		if (empty($name)) {
			return $this->items['ALL'];
		}
		$tmp = explode('.', $name);
		if (count($tmp) == 1) {
			array_unshift($tmp, 'all');
		}
		$action = array_shift($tmp);
		return $this->__call($action, [implode('.', $tmp), $default, $functions]);
	}
	public function setRequest($name, $value) {
		$tmp = explode('.', $name);
		$action = strtoupper(array_shift($tmp));
		if (isset($this->items[$action])) {
			$this->items[$action] = array_set($this->items[$action], implode('.', $tmp), $value);
			if ($action != 'ALL') {
				$this->items['ALL'] = array_merge($this->items['ALL'], $this->items[$action]);
			}
			return true;
		}
		return false;
	}
	public function setGet($name, $value = '') {
		if (is_array($name)) {			
			$this->items['GET'] = array_merge($this->items['GET'], $name);
			$this->items['ALL'] = array_merge($this->items['ALL'], $name);
			$_GET = $this->items['GET'];
		} elseif (null === $value) {
			if (isset($this->items['GET'][$name])) unset($this->items['GET'][$name]);
			if (isset($this->items['ALL'][$name])) unset($this->items['ALL'][$name]);
			if (isset($_GET[$name])) unset($_GET[$name]);
		} else {
			$this->items['GET'][$name] = $value;
			$this->items['ALL'][$name] = $value;
			$_GET[$name] = $value;
		}
	}
	public function __call($action, $arguments) {
		$action = strtoupper($action);
		if (empty($arguments)) {
			return $this->items[$action];
		}		
		$data = array_get($this->items[$action], $arguments[0]);		
		if (null !== $data && !empty($arguments[2])) {
			return batch_functions($arguments[2], $data);
		}
		$default = isset($arguments[1])? $arguments[1] : null;
		return (null === $data)? $default : $data;
	}
	public function isDomain() {
		if (isset($_SERVER['HTTP_REFERER'])) {
			$referer = parse_url($_SERVER['HTTP_REFERER']);
			return $referer['host'] == $_SERVER['HTTP_HOST'];
		}
		return false;
	}
	public function getHost($url = '') {
		if (empty($url)) return $_SERVER['HTTP_HOST'];
		$arr = parse_url($url);
		return isset($arr['host']) ? $arr['host'] : '';
	}
	public function getHeader($name = '', $default = '') {
		$server = $_SERVER;
		if(strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false && function_exists('apache_response_headers')) {
			$response = call_user_func('apache_response_headers');
			$server = array_merge($server, $response);
		}
		$headers = [];
		foreach ($server as $key => $value) {
			if (substr($key, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($key, 5))))] = $value;
			}
		}
		if (empty($name)) return $headers;
		$name = strtolower($name);
		return isset($headers[$name])? $headers[$name] : $default;
	}
}