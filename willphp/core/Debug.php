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
class Debug {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new DebugBuilder();
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
class DebugBuilder {
	protected $items = ['sql'=>[], 'debug'=>[], 'error'=>[]];
	public function trace($info, $level = 'debug') {
		$level = strtolower($level);
		if (!in_array($level, ['sql', 'error']) || is_array($info)) {
			$this->items['debug'][] = $info;
		} else {
			$this->items[$level][] = $info;
		}
	}
	public function appendTrace($content = '') {
		if (!APP_TRACE || IS_AJAX) {
			return $content;
		}
		if (is_scalar($content) && !is_bool($content) && !preg_match('/^http(s?):\/\//', $content)) {
			$pos = strripos($content, '</body>');
			$trace = $this->getTrace();
			if (false !== $pos) {
				$content = substr($content, 0, $pos).$trace.substr($content, $pos);
			} else {
				$content = $content.$trace;
			}
		}
		return $content;
	}
	public function getTrace() {
		if (!APP_TRACE || IS_AJAX) {
			return '';
		}
		$trace = $this->parseTrace();
		$end_time = round((microtime(true) - START_TIME) , 4);
		$errno = '';
		if (!empty($this->items['error'])) {
			$errno = ' <span style="color:red">'.count($this->items['error']).'</span>';
		}
		ob_start();
		include ROOT_PATH.'/willphp/core/view/debug.php';
		return "\n".ob_get_clean()."\n";
	}
	protected function parseTrace() {
		$level = Config::get('debug.level', []);		
		if (!isset($level['base'])) {
			$level['base'] = '基本';
		}		
		$this->items['base']['主机信息'] = $_SERVER['SERVER_SOFTWARE'];
		$this->items['base']['请求信息'] = $_SERVER['SERVER_PROTOCOL'].' '.$_SERVER['REQUEST_METHOD'].': <a href="'.__URL__.'" style="color:#000;">'.__URL__.'</a>';
		$this->items['base']['路由参数'] = Route::getPath();
		$end_memory = memory_get_usage() - START_MEMORY;
		$end_time = round((microtime(true) - START_TIME) , 4);
		$files = get_included_files();
		$filesize = 0;
		foreach ($files as $k=>$file) {
			$k ++;
			$fs = filesize($file);
			if (isset($level['file'])) {
				$this->items['file'][] = $k.'.'.$file.' ( '.number_format($fs / 1024, 2).' KB )';
			}
			$filesize += $fs;
		} 
		$this->items['base']['内存开销'] = number_format($end_memory/1024,2).' KB <a href="'.__URL__.'/api/clear">清除缓存</a>';
		$this->items['base']['调试统计'] = '文件：'.count($files).'('.number_format($filesize/1024, 2).' KB)';
		$this->items['base']['运行时间'] = $end_time.'s at '.date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']).' <a href="http://www.113344.com" style="color:green;" target="_blank">WillPHP'.__VERSION__.'</a>';
		$this->items['sql'] = $this->filter($this->items['sql'], []);
		$this->items['debug'] = $this->filter($this->items['debug'], []);		
		$this->items['error'] = Error::getError(); //获取错误
		$this->items['error'] = $this->filter($this->items['error'], []);
		if (isset($level['post'])) {
			$this->items['post'] = $this->filter($_POST, []);
		}
		if (isset($level['post'])) {
			$this->items['get'] = $this->filter($_GET, []);
		}
		if (isset($level['cookie'])) {
			$this->items['cookie'] = $this->filter($_COOKIE, []);
		}
		if (isset($level['session'])) {
			$this->items['session'] = session_id()? $this->filter($_SESSION, []) : [];
		}
		$trace = [];
		foreach ($level as $k => $name) {
			$title = $name;
			$total = 0;
			if ($k != 'base') {
				$total = count($this->items[$k]);
				$title = $name.'('.$total.')';
			}
			if ($total > 0 || !in_array($k, ['post', 'get', 'cookie', 'session'])) {
				$trace[$title] = $this->items[$k];
			}
			if (!in_array($k, ['base','file']) && $total > 0) {
				$trace[$level['base']]['调试统计'] .=  ' | '.$name.'：'.$total;
			}
		}
		return $trace;
	}
	protected function filter($data, $default = '') {
		if (empty($data)) {
			return $default;
		}
		if (is_array($data)) {
			array_walk_recursive($data, 'self::filterValue'); //输出前处理
		} else {
			self::filterValue($data, '');
		}
		return $data;
	}
	protected static function filterValue(&$value, $key) {
		$value = ($value === null)? '' : htmlspecialchars($value, ENT_QUOTES);
	}
}