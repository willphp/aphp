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
class Dir {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new DirBuilder();
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
class DirBuilder {
	public function create($dir, $auth = 0755) {
		if (!empty($dir)) {
			return is_dir($dir) or mkdir($dir, $auth, true);
		}
		return false;
	}
	public function del($dir) {
		if (!is_dir($dir)) {
			return true;
		}
		$files = array_diff(scandir($dir), ['.', '..']);
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? $this->del("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}
	public function delFile($file) {
		if (is_file($file)) {
			return unlink($file);
		}
		return true;
	}
	public function getMtime($dir) {
		if (!is_dir($dir)) return 0;
		$mtime = 0;
		$files = array_diff(scandir($dir), ['.', '..']);
		foreach ($files as $file) {
			$filemtime = filemtime($dir.'/'.$file);
			if ($filemtime > $mtime) {
				$mtime = $filemtime;
			}
		}
		return $mtime;
	}
	public function getDirsMtime(array $dirs) {
		$mtime = [0];
		foreach ($dirs as $file) {
			if (is_dir($file)) {
				$mtime[] = $this->getMtime($file);
			} elseif (file_exists($file)) {
				$mtime[] = filemtime($file);
			}
		}
		return max($mtime);
	}
}