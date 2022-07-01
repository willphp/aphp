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
 * 自动过滤
 */
trait Filter {
	protected $filter = [];	 //自动过滤设置
	/**
	 * 自动过滤
	 * @return void
	 */
	final protected function autoFilter() {	
		if (empty($this->filter)) {
			return;
		}
		$data = &$this->original;
		foreach ($this->filter as $filter) {
			$field = $filter[0]; //字段
			$at = isset($filter[1]) ? $filter[1] : AT_SET; //条件
			$action = isset($filter[2]) ? $filter[2] : IN_BOTH; //时机	
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
			if ($action == $this->action() || $action == IN_BOTH) {
				unset($data[$filter[0]]);
			}
		}		
		return true;
	}
}