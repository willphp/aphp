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
class Cache {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new CacheBuilder();
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
class CacheBuilder {
	protected $dir;
	public function __construct() {
		$this->dir(RUNTIME_PATH.'/cache');
	}
	public function dir($dir) {
		if (!Dir::create($dir)) {
			throw new \Exception('缓存目录创建失败或不可写');
		}
		$this->dir = $dir;
		return $this;
	}
	//根据名称获取缓存文件 widget/user.abc	
	protected function getCacheFile($name) {
		$dir = $this->dir;
		if (false !== strpos($name, '.')) {
			list($type, $name) = explode('.', $name);
			$dir .= '/'.$type;
			if (!is_dir($dir)) mkdir($dir, 0755, true);
		}
		return $dir.'/'.md5($name).'.php';
	}	
	//设置
	public function set($name, $data, $expire = 0) {
		$file = $this->getCacheFile($name);
		$expire = sprintf("%010d", $expire);
		$content = $expire.serialize($data);
		$result = file_put_contents($file, $content);
		return $result? $data : false;
	}
	//获取
	public function get($name) {
		$file = $this->getCacheFile($name);
		if (!is_file($file) || !is_readable($file)) {
			return false;
		}
		$content = file_get_contents($file);
		$expire  = intval(substr($content, 0, 10));
		$mtime = filemtime($file);
		if ($expire > 0 && $mtime + $expire < time()) {
			if (is_file($file)) {
				unlink($file);
			}
			return false;
		}
		return unserialize(substr($content, 10));
	}	
	//是否存在
	public function has($name) {
		$data = $this->get($name);
		return (bool)$data;
	}
	//删除
	public function del($name) {
		$file = $this->getCacheFile($name);
		return Dir::delFile($file);
	}
	//清理
	public function flush($type = '', $dir = '') {
		if (empty($dir)) {
			$dir = $this->dir;
		}
		return Dir::del($dir.'/'.$type);
	}
	//清除所有
	public function clearAll($type = '', $apps = []) {
		if (empty($apps)) {
			$apps = Config::get('app.app_list', []);
		}
		if (!empty($apps)) {
			foreach ($apps as $app) {
				$this->flush($type, ROOT_PATH.'/runtime/'.$app.'/cache');
			}
			return true;
		}
		return $this->flush($type);
	}
}