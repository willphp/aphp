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
class Error {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new ErrorBuilder();
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
class ErrorBuilder {
	protected $errors = [];
	public function bootstrap() {
		error_reporting(0);
		set_error_handler([$this, 'appError'], E_ALL);
		set_exception_handler([$this, 'appException']);
	}
	public function getError() {
		return $this->errors;
	}
	public function appError($errno, $error, $file, $line) {
		$info = [];
		$info['errno'] = $errno;
		$info['file'] = $file;
		$info['line'] = $line;
		$info['type'] = 'ERROR';
		$info['error'] = $error;
		$info['msg'] = 'ERROR: ['.$errno.']'.$error.'['.$file.':'.$line.']';
		$this->errors[] = $info['msg'];
		if ($errno == E_NOTICE) {
			if (PHP_SAPI != 'cli' && APP_DEBUG && Config::get('error.show_notice') && !APP_TRACE) {
				echo '<p style="color:#900">['.$info['type'].'] '.$error.' ['.basename($file).':'.$line.']<p>';
			}
		} elseif (!in_array($errno, [E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED])) {
			$this->showError($info);
		}
	}
	public function appException($e) {
		$info = [];
		$info['errno'] = $e->getCode();
		$info['error'] = $e->getMessage();
		$info['file'] = $e->getFile();
		$info['line'] = $e->getLine();
		$info['path'] = $e->__toString();
		$info['type'] = 'EXCEPTION';
		$info['msg'] = 'EXCEPTION: ['.$info['errno'].']'.$info['error'].'['.$info['file'].':'.$info['line'].']';
		$this->errors[] = $info['msg'];
		$this->showError($info);
	}
	protected function showError(array $info) {
		if (PHP_SAPI == 'cli') {
			die(PHP_EOL."\033[;36m ".$info['msg']." \x1B[0m\n".PHP_EOL);
		}
		if (!APP_DEBUG || IS_AJAX) Log::write($info['msg'], $info['type']); //写入日志
		$msg = APP_DEBUG? $info['msg'] : Config::get('error.msg', '系统错误，请稍候访问');
		ob_clean();
		if (IS_AJAX) {
			App::showJson(500, $msg);
		} elseif (APP_DEBUG) {
			include ROOT_PATH.'/willphp/core/view/error.php';
		} else {
			include ROOT_PATH.'/willphp/core/view/500.php';
		}		
		die;
	}
}