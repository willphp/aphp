<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace willphp\core\validate;
use willphp\core\Db;
class ValidateRule {	
	//字段唯一验证 unique:user,id(表名，主键)
	public function unique($value, $field, $params, $data) {
		$args = explode(',', $params);
		$map = [];
		$map[$field] = $value;
		if (isset($data[$args[1]])) {
			$map[] = [$args[1], '<>', $data[$args[1]]];
		}
		$isFind = Db::table($args[0])->field($args[1])->where($map)->find();
		return (!$isFind || empty($value))? true : false;
	}
	//验证码验证
	public function captcha($value, $field, $params, $data) {
		return isset($data[$field]) && strtoupper($data[$field]) == session('captcha');
	}	
	//必须有字段，不能为空
	public function required($value, $field, $params, $data) {
		return isset($data[$field]) && trim($data[$field]) !== '';
	}
	//必须有字段
	public function exists($value, $field, $params, $data) {
		return isset($data[$field]);
	}
	//必须无字段
	public function notExists($value, $field, $params, $data) {
		return !isset($data[$field]);
	}	
	//字段比对
	public function confirm($value, $name, $params, $data) {
		return ($value == $data[$params]);
	}	
	//正则验证
	public function regex($value, $name, $params) {
		return preg_match($params, $value)? true : false;
	}
	//最大长度
	public function max($value, $field, $params) {
		$len = mb_strlen(trim($value), 'utf-8');
		return ($len <= $params);
	}
	//最小长度
	public function min($value, $field, $params) {
		$len = mb_strlen(trim($value), 'utf-8');
		return ($len >= $params);
	}
	//固定长度或长度范围
	public function len($value, $field, $params) {
		$len = mb_strlen(trim($value), 'utf-8');
		$params = explode(',', $params);
		if (isset($params[1])) {
			return ($len >= $params[0] && $len <= $params[1]);
		}
		return ($len == $params[0]);		
	}	
	//数字范围
	public function between($value, $field, $params) {
		if (!preg_match('/[0-9\-]+/', $value)) {
			return false;
		}	
		$params = explode(',', $params);
		return ($value >= $params[0] && $value <= $params[1]);
	}	
	//在里面
	public function in($value, $field, $params) {
		$params = explode(',', $params);
		return in_array($value, $params);
	}
	//不在里面
	public function notin($value, $field, $params) {
		$params = explode(',', $params);
		return !in_array($value, $params);
	}	
	//filter_var验证 $params = 'url,email,ip,float,int,boolean
	public function filter($value, $field, $params) {
		$params = strtolower($params);
		$filter = [];
		$filter['url'] = FILTER_VALIDATE_URL;
		$filter['email'] = FILTER_VALIDATE_EMAIL;
		$filter['ip'] = FILTER_VALIDATE_IP;
		$filter['float'] = FILTER_VALIDATE_FLOAT;
		$filter['int'] = FILTER_VALIDATE_INT;
		$filter['boolean'] = FILTER_VALIDATE_BOOLEAN;
		if (isset($filter[$params])) {
			return filter_var($value, $filter[$params])? true : false;
		}
		return false;
	}
}