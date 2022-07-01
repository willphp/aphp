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
class Middleware {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new MiddlewareBuilder();
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
class MiddlewareBuilder {
	protected $params; //传递参数
	/**
	 * 执行中间件
	 * @param array $middleware 中间件方法
	 * @return bool
	 */
	protected function exe($middleware) {
		$middleware = array_unique($middleware);		
		$dispatcher = array_reduce(array_reverse($middleware), $this->callback(), function () {});
		$dispatcher();
		return true;
	}
	/**
	 * 装饰者闭包
	 * @return \Closure
	 */
	protected function callback() {
		return function ($callback, $class) {
			return function () use ($callback, $class) {
				if (is_callable([$class, 'run'])) {
					$content = call_user_func_array([new $class, 'run'], [$callback, $this->params]);
					$this->output($content);
				}
			};
		};
	}
	/**
	 * 输出内容
	 * @param mixed $content 内容
	 */
	protected function output($content = '') {
		if ($content) {
			if (is_scalar($content)) {
				if (preg_match('/^http(s?):\/\//', $content)) {
					header('location:'.$content);
				} else {
					echo $content;
				}
			} elseif (is_object($content) && method_exists($content, '__toString')) {
				echo $content;
			} else {
				header('Content-type: application/json;charset=utf-8');
				echo json_encode($content, JSON_UNESCAPED_UNICODE);
			}
			exit();
		}
	}
	/**
	 * 执行全局中间件
	 * @return bool
	 */
	public function globals() {		
		$middleware = array_unique(Config::get('middleware.global', []));		
		return $this->exe($middleware);
	}
	/**
	 * 添加应用(web)中间件
	 * @param string $name			中间件名称
	 * @param array  $middleware 	中间件处理类
	 * @return bool
	 */
	public function add($name, array $middleware) {
		$web = Config::get('middleware.web.'.$name, []);
		foreach ($middleware as $class) {
			array_push($web, $class);
		}
		return Config::set('middleware.web.'.$name, array_unique($web));
	}
	/**
	 * 执行应用(web)中间件
	 * @param string $name   中间件名称
	 * @param mixed  $params 中间件参数
	 * @return bool
	 */
	public function web($name, $params = '') {
		$web = Config::get('middleware.web.'.$name, []);
		if (!empty($web)) {		
			$this->params = $params;
			return $this->exe($web);
		}
	}
	/**
	 * 执行控制器中间件
	 * @param       $name  中间件名称
	 * @param array $mode  模式
	 *                     ['only'=>['a','b']] 仅执行a,b控制器动作
	 *                     ['except']=>['a','b']], 除了a,b控制器动作
	 * @return bool
	 */
	public function set($name, $mode = []) {
		$exe = []; //执行的控制器中间件
		$middleware = Config::get('middleware.controller.'.$name, []); //当前控制器中间件
		if (!$mode) {
			$exe = $middleware;
		} else {			
			$action = Route::getAction();
			foreach ($mode as $type => $ctrl_list) {
				$ctrl_list = array_map('strtolower', $ctrl_list); //控制器列表
				if ($type == 'only' && in_array($action, $ctrl_list)) {
					$exe = array_merge($exe, $middleware);
				}
				if ($type == 'except' && !in_array($action, $ctrl_list)) {
					$exe = array_merge($exe, $middleware);
				}
			}
		}
		return $this->exe(array_unique($exe));
	}	
}