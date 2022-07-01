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
class App {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new AppBuilder();
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
class AppBuilder {
	protected static $instances = []; //单例集合
	protected $name; //当前应用(模块)
	protected $booted = false; //已启动	
	//设置应用(模块)名
	public function name($name = '') {
		$this->name = $this->parseName($name);
		return $this;	
	}
	//解析应用(模块)名
	protected function parseName($name) {
		if (is_array($name)) {
			$keys = [__HOST__, explode('.', __HOST__)[0], '*'];
			foreach ($keys as $key) {
				if (isset($name[$key])) return $name[$key];
			}
		}
		return (is_string($name) && '' !== $name)? strtolower($name) : basename($_SERVER['SCRIPT_FILENAME'], '.php');
	}	
	//启动
	public function bootstrap() {
		if ($this->booted) return;
		if (!$this->name) $this->name = $this->parseName();
		$this->init();	
		if(!is_dir(APP_PATH.'/controller')) Build::app(); //生成应用		
		Error::bootstrap();
		Middleware::globals();
		$res = Route::bootstrap()->executeControllerAction();
		Response::output($res);
		$this->booted = true;
	}
	//初始化
	protected function init() {
		define('APP_NAME', $this->name);
		define('RUNTIME_PATH', ROOT_PATH.'/runtime/'.APP_NAME);
		define('APP_PATH', ROOT_PATH.'/app/'.APP_NAME);	
		$config = $this->loadConfig();
		define('APP_DEBUG', !empty($config['app']['debug'])? $config['app']['debug'] : false);
		define('APP_TRACE', !empty($config['app']['trace'])? $config['app']['trace'] : false);
		define('URL_REWRITE', !empty($config['app']['url_rewrite'])? $config['app']['url_rewrite'] : false);		
		define('__WEB__', URL_REWRITE? str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']) : $_SERVER['SCRIPT_NAME']);
		define('__URL__', IS_HTTPS? 'https://'.__HOST__.__WEB__ : 'http://'.__HOST__.__WEB__);	
		define('VIEW_PATH', !empty($config['site']['view_path'])? $config['site']['view_path'] : APP_PATH.'/view');
		define('THEME_ON', !empty($config['site']['theme_on'])? $config['site']['theme_on'] : false);
		define('__THEME__', !empty($config['site']['theme'])? $config['site']['theme'] : 'default');
		define('THEME_PATH', THEME_ON? VIEW_PATH.'/'.__THEME__ : VIEW_PATH);
	}
	//加载配置
	protected function loadConfig() {
		$configPaths = [ROOT_PATH.'/config', APP_PATH.'/config', ROOT_PATH.'/.env'];		
		$mtime = Dir::getDirsMtime($configPaths); 
		$config = Cache::get('config.'.$mtime);
		if (!$config) {
			Cache::flush('config');
			foreach ($configPaths as $path) {
				Config::load($path);
			}
			$config = Config::all();
			Cache::set('config.'.$mtime, $config);
		} else {
			Config::reset($config);
		}
		return $config;
	}
	//获取单例类
	public function make($class) {
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class();
		}
		return self::$instances[$class];
	}
	//统一Json显示
	public function showJson($code, $msg = '', $data = null, $extend = []) {
		header('Content-type: application/json;charset=utf-8');
		$json = Config::get('json', ['ret'=>'ret','msg'=>'msg','data'=>'data', 'status'=>'status']);
		$res = [];
		$res[$json['ret']] = $code;
		$res[$json['msg']] = $msg;
		if (null !== $data) {
			$res[$json['data']] = $data;
		}
		$res[$json['status']] = ($code < 400)? 1 : 0;
		$res = array_merge($res, $extend);
		exit(json_encode($res, JSON_UNESCAPED_UNICODE));
	}
	//错误处理
	public function halt($msg, $type = 'fail') {
		if (is_array($msg)) $msg = current($msg);
		if (PHP_SAPI == 'cli') {
			die(PHP_EOL."\033[;36m ".$msg." \x1B[0m\n".PHP_EOL); //命令行错误
		}	
		$class = 'app\\'.APP_NAME.'\\controller\\Error';		
		$action = in_array($type, ['fail','empty','validate'])? $type : 'fail';
		if (!method_exists($class, $action)) {
			if (IS_AJAX) {
				$this->showJson(400, $msg);
			} else {
				include ROOT_PATH.'/willphp/core/view/500.php';
			}
		} else {
			$handler = $this->make($class);
			$res = call_user_func_array([$handler, $action], [$msg]);
			Response::output($res);
		}
		exit;
	}
}