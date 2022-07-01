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
/**
 * 自动处理
 */
trait Auto {
	protected $auto = []; //自动处理
	/**
	 * 自动处理
	 * @return void/mixed
	 */
	final protected function autoOperation() {
		if (empty($this->auto)) {
			return;
		}
		$data = &$this->original;
		foreach ($this->auto as $auto) {
			$field = $auto[0]; //字段
			$at = isset($auto[3]) ? $auto[3] : AT_SET; //处理条件
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
			$rule = $auto[1]; //处理规则
			$type = isset($auto[2]) ? $auto[2] : 'string'; //处理方式
			$action = isset($auto[4]) ? $auto[4] : IN_BOTH; //处理时机
			if ($action == $this->action() || $action == IN_BOTH) {
				if (empty($data[$field])) {
					$data[$field] = '';
				}
				if ($type == 'string') {
					$data[$field] = $rule;
				} elseif ($type == 'method') {
					$data[$field] = call_user_func_array([$this, $rule], [$data[$field], $data]);
				} elseif ($type == 'function') {
					$rule = explode('|', $rule); //处理规则
					foreach ($rule as $fn) {
						if (!function_exists($fn)) {
							throw new \Exception($fn.' 函数不存在');
						}
						$data[$field] = $this->need_params($fn)? $fn($data[$field]) : $fn();
					}
				}
			}
		}
		return true;
	}
	/**
	 * 检测函数是否需要参数
	 * @param string $func_name
	 * @return boolean
	 */
	protected function need_params($func_name) {
		$reflect = new \ReflectionFunction($func_name);
		return !empty($reflect->getParameters());
	}
}