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
class Filter {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new FilterBuilder();
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
class FilterBuilder {
	//过滤输入
	public function input(array $data) {
		array_walk_recursive($data, 'self::parseIn');
		return $data;
	}
	//过滤输出
	public function output(array $data) {
		array_walk_recursive($data, 'self::parseOut');
		return $data;
	}
	//是否是html字段	
	protected static function isHtmlField($field) {
		$htmlFields = Config::get('filter.html_fields', []);
		$htmlLike = Config::get('filter.html_like', '');	
		return in_array($field, $htmlFields) || (!empty($htmlLike) && false !== strpos($field, $htmlLike));
	}	
	//输入处理
	public static function parseIn(&$value, $key) {		
		if (!empty($value)) {
			$funcHtml = Config::get('filter.func_html', '');
			$funcExceptHtml = Config::get('filter.func_except_html', '');
			if (!empty($funcHtml) && self::isHtmlField($key)) {
				$value = batch_functions($funcHtml, $value);
			} elseif (!empty($funcExceptHtml) && !self::isHtmlField($key)) {
				$value = batch_functions($funcExceptHtml, $value);
			}
			if (!is_numeric($key)) {
				$fieldIn = Config::get('filter.field_in', []);
				foreach ($fieldIn as $field => $func) {
					if ($key == $field || in_array($key, explode(',', $field))) {
						$value = batch_functions($func, $value);
					}
				}
			}	
		}	
	}	
	//输出处理
	public static function parseOut(&$value, $key) {
		if (!empty($value)) {
			$funcOut = Config::get('filter.func_out', '');
			$funcExceptHtmlOut = Config::get('filter.func_except_html_out', '');
			if (!empty($funcExceptHtmlOut) && is_scalar($value) && !is_numeric($value) && !self::isHtmlField($key)) {
				$value = batch_functions($funcExceptHtmlOut, $value);
			}
			if (!empty($funcOut) && is_scalar($value) && !is_numeric($value)) {
				$value = batch_functions($funcOut, $value);
			}
		}
	}
}