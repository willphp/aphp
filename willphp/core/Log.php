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
class Log {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new LogBuilder();
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
class LogBuilder {
	protected $dir; 
	protected $log = [];
	public function __construct() {			
		$this->dir(RUNTIME_PATH.'/log');
	}
	public function dir($dir) {
		if (!Dir::create($dir)) {
			throw new \Exception('日志目录创建失败或不可写');
		}
		$this->dir = $dir;
		return $this;
	}
	public function record($message, $level = 'ERROR') {
		$this->log[] = date('[ c ]').$level.':'.$message.PHP_EOL;
		return true;
	}
	public function write($message, $level = 'ERROR') {
		$file = $this->dir.'/'.date('Y_m_d').'.log';
		return error_log(date('[ c ]').$level.':'.$message.PHP_EOL, 3, $file, null);
	}
	public function __destruct() {
		if (!empty($this->log)) {
			$file = $this->dir.'/'.date('Y_m_d').'.log';
			return error_log(implode('', $this->log), 3, $file, null);
		}
	}	
}