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
class Response {
	public static function output($res = null) {
		if (is_object($res) && method_exists($res, '__toString')) {
			$res = $res->__toString();
		}
		if (is_scalar($res)) {
			if (preg_match('/^http(s?):\/\//', $res)) {
				header('location:'.$res);
			} else {	
				$trace_show = Config::get('debug.trace_show', true);
				if (APP_TRACE && $trace_show) {
					$res = Debug::appendTrace($res);
				}
				echo $res;
			}
		} elseif (is_null($res)) {
			return;
		} else {
			header('Content-type: application/json;charset=utf-8');
			echo json_encode($res, JSON_UNESCAPED_UNICODE);
			exit();
		}
	}
	public static function json($data = '') {
		return json_encode($data, JSON_UNESCAPED_UNICODE);
	}	
}