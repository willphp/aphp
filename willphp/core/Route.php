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
class Route {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new RouteBuilder();
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
class RouteBuilder {
	protected $module; //当前模块
	protected $controller; //当前控制器
	protected $action; //当前方法	
	protected $uri; //当前uri
	protected $route; //当前路由+参数(array)
	protected $path; //当前路径+参数(string)
	protected $rule; //路由规则
	public function __construct() {
		$this->module = strtolower(APP_NAME);
		$this->controller = Config::get('route.default_controller', 'index');
		$this->action = Config::get('route.default_action', 'index');	
		$this->rule = $this->parseRuleFile();
	}
	/**
	 * 路由启动
	 */
	public function bootstrap() {
		$this->uri = $this->getUri();
		if (!$this->route) {
			$route = $this->parseUri($this->uri, $_GET);
			$this->route = $route;
			$this->module = $route['module'];
			$this->controller = $route['controller'];
			$this->action = $route['action'];
			$this->path = $route['path'];
		}
		return $this;
	}
	/**
	 * 获取路由+参数
	 * @param string $route
	 * @return array
	 */
	public function getRoute($route = '') {
		return empty($route)? $this->route : $this->parseUri($route);
	}
	/**
	 * 获取路径+参数
	 * @param string $route
	 * @return string
	 */
	public function getPath($route = '') {
		return empty($route)? $this->path : $this->parseUri($route, [], true);
	}
	/**
	 * 获取控制器
	 * @return string
	 */
	public function getController() {
		return $this->controller;
	}
	/**
	 * 获取方法
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}
	//处理路由规则
	protected function parseRuleFile() {
		$rule = ['just' => [], 'flip' => []];
		$mtime = 0;
		$file = ROOT_PATH.'/route/'.$this->module.'.php';
		if (!file_exists($file)) {
			return $rule;
		}
		$mtime = filemtime($file);
		$rule = Cache::get('route.'.$mtime); //获取缓存
		if (!$rule) {
			$conf = include $file;
			if (empty($conf)) {
				return ['just' => [], 'flip' => []];
			}
			Cache::flush('route');
			$expkey = [':num', ':float', ':string', ':alpha', ':page', ':any'];
			$expval = ['[0-9\-]+', '[0-9\.\-]+', '[a-zA-Z0-9\-_]+', '[a-zA-Z\x7f-\xff0-9-_]+', '[0-9]+', '.*'];
			$just = $flip = [];
			foreach ($conf as $k => $v) {
				if (strpos($k, ':') !== false) {
					$k = str_replace($expkey, $expval, $k);
				}
				$k = trim(strtolower($k), '/');
				$just[$k] = trim(strtolower($v), '/');
			}
			$tmp = array_flip($just);
			foreach ($tmp as $k => $v) {
				if (preg_match_all('/\(.*?\)/i', $v, $res)) {
					$exp = [];
					$count = count($res[0]);
					for ($i=1;$i<=$count;$i++) {
						$exp[] = '/\$\{'.$i.'\}/i';
					}
					$k = preg_replace($exp, $res[0], $k);
					$i = 0;
					$v = preg_replace_callback('/\(.*?\)/i',function ($matches) use(&$i) {
						$i ++;
						return '${'.$i.'}';
					}, $v);
				}
				$flip[$k] = $v;
			}
			$rule = ['just' => $just, 'flip' => $flip];
			Cache::set('route.'.$mtime, $rule);
		}
		return $rule;
	}
	/**
	 * 获取当前访问的Uri路径
	 * @return string
	 */
	protected function getUri() {		
		$uri = $this->controller.'/'.$this->action;
		$pathinfo = '';
		$pathinfo_var = Config::get('route.pathinfo_var', 's');		
		if (isset($_SERVER['PATH_INFO'])) {
			$pathinfo = preg_replace('/\/+/', '/', trim($_SERVER['PATH_INFO'], '/'));
		} elseif (isset($_GET[$pathinfo_var])) {
			$pathinfo = preg_replace('/\/+/', '/', trim($_GET[$pathinfo_var], '/'));
			unset($_GET[$pathinfo_var]);
		}	
		$validate_get = Config::get('route.validate_get', '#^[a-zA-Z0-9\x7f-\xff\%\/\.\-_]+$#');		
		if ($pathinfo && preg_match($validate_get, $pathinfo)) {
			$del_suffix = Config::get('route.del_suffix', '.html');		
			$uri = str_replace($del_suffix, '', $pathinfo);
		}
		$uri = $this->ruleReplace($uri, $this->rule['just']);
		return $uri;
	}
	/**
	 * 处理uri成route
	 * @param string $uri 要处理的uri
	 * @param array $params 参数
	 * @param bool $getPath 是否只返回路径
	 * @return array|string
	 */
	public function parseUri($uri, $params = [], $getPath = false) {
		$args1 = $args2 = []; //参数
		$route = [];
		$route['module'] = $this->module;
		$route['controller'] = $this->controller;
		$route['action'] = $this->action;
		if (false !== strpos($uri, '?')) {
			list($uri, $args2) = explode('?', $uri);
			parse_str($args2, $args2);
		}
		$uri = trim($uri, '/');
		$path = explode('/', $uri);
		$isCurrent = true; //是否在当前模块
		if (false !== strpos($path[0], '.')) {
			list($route['module'], $path[0]) = explode('.', $path[0]);
			$isCurrent = false;
		}
		$count = count($path);
		if ($count == 1) {
			if ($isCurrent) {
				$route['action'] = array_shift($path);
			} else {
				$route['controller'] = array_shift($path);
			}
		} elseif ($count >= 2) {
			$route['controller'] = array_shift($path);
			$route['action'] = array_shift($path);
			$over = count($path);
			for($i=0;$i<$over;$i+=2) {
				$args1[$path[$i]] = isset($path[$i+1])? $path[$i+1] : '';
			}
		}
		$route['controller'] = name_snake($route['controller']);
		$route['params'] = array_merge($args1, $args2, $params);
		$route['path'] = $route['module'].'/'.$route['controller'].'/'.$route['action'];
		if (!empty($route['params'])) {
			ksort($route['params']);
			$route['path'] .= '?'.http_build_query($route['params']);
		}		
		$this->array_change_value_case_recursive($route); 
		return $getPath? $route['path'] : $route;
	}
	/**
	 * 值转换成小写
	 * @param array $array
	 * @param string $case
	 */
	protected function array_change_value_case_recursive(&$array, $case = CASE_LOWER) {
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				$this->array_change_value_case_recursive($array[$k], $case);
				continue;
			}			
			$array[$k] = ($case == CASE_LOWER)? strtolower($v) : strtoupper($v);				
		}
	}
	/**
	 * 获取页面缓存
	 * @return string
	 */
	protected function getViewCache() {
		if (IS_GET && Config::get('view.view_cache')) {			
			return Cache::get('view.'.md5($this->path));
		}
		return false;
	}
	/**
	 * 执行控制器方法
	 * @param string $uri
	 * @param array $params
	 * @throws \Exception
	 * @return boolean|mixed
	 */
	public function executeControllerAction($uri = '', $params = []) {
		$isCall = !empty($uri); //是否是调用
		if (!$isCall && ($cache = $this->getViewCache())) {
			return $cache;
		}
		$route = !$isCall? $this->route : $this->parseUri($uri, $params);
		$module = $route['module'];
		$controller = name_camel($route['controller']);
		$class = 'app\\'.$module.'\\controller\\'.$controller;	
		$action = $route['action'];
		$params = $route['params'];	
		$path = $route['controller'].'/'.$route['action'];
		if (!$isCall) {
			if (!in_array($module, Config::get('app.app_list', ['home']))) {				
				return App::halt($module.' 模块禁止');
			}
			if (0 === strpos($action, '_')) {				
				return App::halt($path.' 不可访问');
			}
		}
		if (!method_exists($class, $action)) {
			return $isCall? false : App::halt($path, 'empty');
		}
		$class = App::make($class);
		try {
			$class_method = new \ReflectionMethod($class, $action);
			if (!$class_method->isPublic()) {
				return App::halt($path.' 不可访问');
			}
			$method_args = $class_method->getParameters(); //参数
			$isReq = false;
			$binds = $extend = [];
			foreach ($method_args as $arg) {
				$arg_name = $arg->getName();
				if ('req' == $arg_name) {
					$isReq = true;
					continue;
				}
				$dependency = $arg->getClass();
				if (isset($params[$arg_name])) {
					$binds[$arg_name] = $params[$arg_name];
				} elseif ($dependency) {
					$binds[$arg_name] = App::make($dependency->name);
				} elseif ($arg->isDefaultValueAvailable()) {
					$binds[$arg_name] = $extend[$arg_name] = $arg->getDefaultValue();					
				} elseif (isset($_POST[$arg_name])) {
					$binds[$arg_name] = $_POST[$arg_name];
				} else {
					return App::halt($path.' 参数不足');
				}
			}
			if (!$isCall) {
				Request::setGet(array_merge($params, $extend));	
				Middleware::web('controller_start');
				$this->exeMiddleware($class); //处理控制器中间件
				if ($isReq) {
					$binds['req'] = $this->getReq();
				}
				if (method_exists($class, '_before')) $class->_before($action);
				if (method_exists($class, '_before_'.$action)) $class->{'_before_'.$action}();
			}
			$res = $class_method->invokeArgs($class, $binds);
			if (!$isCall) {				
				if (method_exists($class, '_after_'.$action)) $class->{'_before_'.$action}();
				if (method_exists($class, '_after')) $class->_after($action);
			}
			return $res;
		} catch (\ReflectionException $e) {			
			throw new \Exception($e->getMessage());
		}
	}
	/**
	 * 获取请求参数
	 * @return array
	 */
	protected function getReq() {
		$req = Request::all();
		if ('req' == Config::get('filter.filter_in')) {			
			$req = Filter::input($req);
		}
		return $req;
	}
	/**
	 * 处理控制器中间件
	 * @param string $controller
	 */
	protected function exeMiddleware($controller) {
		$middlewares = [];
		$class = new \ReflectionClass($controller);
		if ($class->hasProperty('middleware')) {
			$reflectionProperty = $class->getProperty('middleware');
			$reflectionProperty->setAccessible(true);
			$middlewares = $reflectionProperty->getValue($controller);
			if (!is_array($middlewares)) {
				Middleware::set($middlewares);
			} else {
				foreach ($middlewares as $key => $val) {
					if (!is_int($key)) {
						Middleware::set($key, $val);
					} else {
						Middleware::set($val);
					}
				}
			}
		}
	}
	/**
	 * 规则替换
	 * @param string $path
	 * @param array $rule
	 * @return string
	 */
	protected function ruleReplace($path, $rule) {
		if (isset($rule[$path])) return $rule[$path];
		foreach ($rule as $k => $v) {
			if (preg_match('#^'.$k.'$#i', $path)) {
				if (false !== strpos($v, '$') && false !== strpos($k, '(')) {
					$v = preg_replace('#^'.$k.'$#i', $v, $path);
				}
				return $v;
			}
		}
		return $path;
	}
	/**
	 * 根据参数 生成url
	 * @param string|array $params 参数
	 * @return string
	 */
	public function pageUrl($params = []) {
		$route = $this->controller.'/'.$this->action;
		if ($this->module != APP_NAME) {
			$route = $this->module.'/'.$route;
		}
		if (empty($params)) {
			return $this->buildUrl($route);
		}
		return is_array($params)? $this->buildUrl($route, $params) : $this->buildUrl($route.'?'.$params);
	}
	/**
	 * url生成
	 * @param string $route
	 * @param array $params
	 * @param string $suffix
	 * @return string
	 */
	public function buildUrl($route = '', $params = [], $suffix = '*') {
		if (in_array($route, ['','@','@/','/@'])) return __URL__;
		if (false !== filter_var($route, FILTER_VALIDATE_URL)) return $route;
		if ($route == '[back]' || $route == 'javascript:history.back(-1);') return 'javascript:history.back(-1);';		
		if ($route == '[history]') return isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : 'javascript:history.back(-1);';		
		if ($suffix == '*') $suffix = Config::get('route.url_suffix', '.html');
		$args = [];
		if (false !== strpos($route, '?')) {
			list($route, $args) = explode('?', $route);
			parse_str($args, $args);
		}		
		$route = trim($route, '/');
		if (empty($route)) {
			$route = $this->controller.'/'.$this->action;
		}
		if (preg_match('#^[a-zA-Z0-9\-_]+$#', $route)) {
			$route = $this->controller.'/'.$route;
		}		
		$params = array_merge($args, $params);
		if (!empty($params)) {
			$params = array_filter($params); //过滤空值和0
			$params = str_replace(['&', '='], '/', http_build_query($params));
			$route = trim($route.'/'.$params, '/');
		}
		if (substr($route, 0, 1) == '@') {
			$route = trim($route, '@');
			return __URL__.'/'.$route.$suffix;
		}
		$route = $this->ruleReplace($route, $this->rule['flip']);
		return __URL__.'/'.$route.$suffix;
	}	
}