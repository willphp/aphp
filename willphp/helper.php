<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
/**
 * 快速获取容器中的实例
 * @param string $name 类名标识
 * @return object
 */
function app($name) {
	return \willphp\core\App::make($name);
}
/**
 * 执行控制器方法
 * @param string $uri
 * @param array $params
 * @return boolean|mixed
 */
function action($uri = '', $params = []) {
	return \willphp\core\Route::executeControllerAction($uri, $params);
}
/**
 * 获取和设置缓存
 * @param string	$name  参数名
 * @param mixed		$value 参数值
 * @param number	$expire 有效时间
 * @return mixed
 */
function cache($name = '', $value = '', $expire = 0) {
	if ($name === '') {
		return '';
	}
	if (null === $name) {
		return \willphp\core\Cache::flush($value);
	}
	if ('' === $value) {
		return (0 === strpos($name, '?'))? \willphp\core\Cache::has(substr($name, 1)) : \willphp\core\Cache::get($name);
	}
	if (null === $value) {
		return \willphp\core\Cache::del($name);
	}
	return \willphp\core\Cache::set($name, $value, $expire);
}
/**
 * 获取或设置配置
 * @param string $name 参数名
 * @param mixed $value 参数值
 * @return mixed
 */
function config($name = '', $value = '') {
	if (!$name) {
		return \willphp\core\Config::all();
	}
	if ('' === $value) {
		return (0 === strpos($name, '?'))? \willphp\core\Config::has(substr($name, 1)) : \willphp\core\Config::get($name);
	}
	return \willphp\core\Config::set($name, $value);
}
/**
 * 获取和设置网站配置参数
 * @param string	$name  参数名
 * @param mixed		$value 参数值
 * @return mixed
 */
function site($name = '', $value = '') {
	if ($name == '') {
		return \willphp\core\Config::get('site');
	}
	if ($value == '') {
		return (0 === strpos($name, '?'))? \willphp\core\Config::has('site.'.substr($name, 1)) : \willphp\core\Config::get('site.'.$name);
	}
	return \willphp\core\Config::set('site.'.$name, $value);
}
/**
 * 浏览器友好的变量输出
 * @param mixed $vars 要输出的变量
 * @return void
 */
function dump(...$vars) {
	ob_start();
	var_dump(...$vars);
	$output = ob_get_clean();
	$output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
	if (PHP_SAPI == 'cli') {
		$output = PHP_EOL.$output.PHP_EOL;
	} elseif (!extension_loaded('xdebug')) {
		$output = '<pre>'.htmlspecialchars($output, ENT_SUBSTITUTE).'</pre>';
	}
	echo $output;
}
/**
 * 变量输出并结束
 * @param mixed $vars 要输出的变量
 * @return void
 */
function dd(...$vars) {
	dump(...$vars);
	exit();
}
/**
 * 输出用户常量
 * @return string
 */
function print_const() {
	$data = get_defined_constants(true);
	dump($data['user']);
}
/**
 * 获取数据库操作类
 * @param string $table 要操作的表名
 * @param string|array $config 数据库连接配置
 * @return object 返回数据库操作对象
 */
function db($table = '', $config = []) {
	return \willphp\core\Db::connect($config, $table);
}
/**
 * 驼峰命名转下划线命名
 * @param string $name 名称
 * @return string
 */
function name_snake($name) {
	return strtolower(trim(preg_replace('/([A-Z])/', '_\1\2', $name), '_'));
}
/**
 * 下划线命名转驼峰命名
 * @param string $name 名称
 * @return string
 */
function name_camel($name) {
	return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
}
/**
 * 获取数据模型类
 * @param string $name 要操作的模型名称
 * @return object 返回数据模型对象
 */
function model($name) {
	$name = trim($name, '.');
	$app = APP_NAME;
	if (false !== strpos($name, '.')) {
		list($app, $name) = explode('.', $name);
	}
	$class = '\\app\\'.$app.'\\model\\'.name_camel($name);
	return \willphp\core\App::make($class);
}
/**
 * 获取数据模型类
 * @param string $name 要操作的模型名称
 * @return object 返回数据模型对象
 */
function widget($name) {
	$name = trim($name, '.');
	$app = APP_NAME;
	if (false !== strpos($name, '.')) {
		list($app, $name) = explode('.', $name);
	}
	$class = '\\app\\'.$app.'\\widget\\'.name_camel($name);
	return \willphp\core\App::make($class);
}
/**
 * 数据验证
 * @param array $rule 规则
 * @param array $data 数据(默认$_POST)
 * @param bool $isBatch 是否批量验证
 */
function validate($rule, $data = [], $isBatch = false) {
	return \willphp\core\Validate::make($rule, $data, $isBatch);
}
/**
 * 生成url
 * @param string $route @：不过路由直接生成；/：根目录
 * @param array $param 参数['id'=>1]
 * @param string $suffix 后缀*:为系统默认后缀.html
 * @return string 返回生成url
 */
function url($route = '', $param = [], $suffix = '*') {
	return \willphp\core\Route::buildUrl($route, $param, $suffix);
}
/**
 * 快速获取请求
 * @param string $name 请求名称,如:get.id
 * @param mixed	 $value 默认值
 * @param string|array $func 处理函数,如:intval,md5
 * @return mixed
 */
function input($name, $default = null, $func = '') {
	return \willphp\core\Request::getRequest($name, $default, $func);
}
/**
 * 记录trace信息
 * @param  string|array  $info  变量
 * @param  string  $level  Trace级别(debug,sql,error)
 * @return void|array
 */
function trace($info = '', $level = 'debug') {
	return \willphp\core\Debug::trace($info, $level);
}
/**
 * 返回json
 * @param array $data
 */
function json($data = '') {
	return \willphp\core\Response::json($data);
}
/**
 * 显示模板
 * @param string $file 模板文件
 * @param array $vars 变量数组
 * @return $this
 */
function view($file = '', $vars = []) {
	return \willphp\core\View::make($file, $vars);
}
/**
 * 分配变量到模板
 * @param string|array  $vars  变量名
 * @param string $value 值
 * @return $this
 */
function view_with($vars, $value = '') {
	return \willphp\core\View::with($vars, $value);
}
/**
 * 更新模板缓存
 * @param string|array $route 模板路由
 * @return bool
 */
function view_update($route = '') {
	return \willphp\core\View::updateCache($route);
}
/**
 * CSRF 表单
 * @return string
 */
function csrf_field() {
	return "<input type='hidden' name='csrf_token' value='".csrf_token('csrf_token')."'/>\r\n";
}
/**
 * CSRF 值
 * @return mixed
 */
function csrf_token() {
	return session('csrf_token');
}
/**
 * 快捷cookie操作
 * @param string $name 名称
 * @param string $value 要设置的值
 * @param int $time 有效期
 * @return boolean|mixed 返回操作结果
 */
function cookie($name, $value = '', $time = 0) {
	$name = APP_NAME.'_'.$name;
	if ('' === $value) {
		return isset($_COOKIE[$name])? $_COOKIE[$name] : false;
	} elseif (is_null($value)) {
		if (isset($_COOKIE[$name])) {
			setcookie($name, '', time() - 3600);
			unset($_COOKIE[$name]);
		}
	} else {
		if ($time > 0) {
			setcookie($name, $value, time() + $time);
		} else {
			setcookie($name, $value);
		}
		$_COOKIE[$name] = $value;
	}
}
/**
 * 获取和设置session
 * @param string	$name  名称
 * @param mixed		$value 值
 * @return mixed
 */
function session($name, $value = '') {
	if (!session_id()) session_start(); //判断开启session
	if (null === $name) {
		$_SESSION = [];
		if(isset($_COOKIE[session_name()])) {
			setCookie(session_name(), '', time()-42000, '/');
		}
		session_destroy();
		return true;
	}
	if ('' === $value) {
		if (0 === strpos($name, '?')) {
			$name = APP_NAME.'_'.substr($name, 1);
			return isset($_SESSION[$name]);
		}
		$name = APP_NAME.'_'.$name;
		return isset($_SESSION[$name])? $_SESSION[$name] : false;
	}
	$name = APP_NAME.'_'.$name;
	if (null === $value) {
		if (isset($_SESSION[$name])) unset($_SESSION[$name]);
	} else {
		$_SESSION[$name] = $value;
	}
	return true;
}
/**
 * 获取加密后的字符串
 * @param string $string 要加密的字符串
 * @param string $key 加密key
 * @return string 返回加密后字符串
 */
function encrypt($string, $key = '') {
	return \willphp\core\Crypt::encrypt($string, $key);
}

/**
 * 获取解密后的字符串
 * @param string $string 要解密的字符串
 * @param string $key 加密key
 * @return string 返回解密后字符串
 */
function decrypt($string, $key = '') {
	return \willphp\core\Crypt::decrypt($string, $key);
}
/**
 * 函数处理值
 * @param mixed $functions
 * @param mixed $value
 * @return mixed
 */
function batch_functions($functions, $value) {
	$functions = is_array($functions) ? $functions : explode(',', $functions);
	foreach ($functions as $func) {
		if(function_exists($func)) {
			$value = is_array($value) ? array_map($func, $value) : $func($value);
		}
	}
	return $value;
}
/**
 * 键名.获取数组值
 * @param array $data
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function array_get(array $data, $key, $default = null) {
	$tmp = explode('.', $key);
	foreach ($tmp as $k) {
		if (!isset($data[$k])) return $default;
		$data = $data[$k];
	}
	return $data;
}
/**
 * 键名.设置数组值
 * @param array $data
 * @param string $key
 * @param mixed $value
 * @return mixed
 */
function array_set(array $data, $key, $value) {
	$tmp = & $data;
	$exp = explode('.', $key);
	foreach ($exp as $k) {
		if (!isset($tmp[$k])) $tmp[$k] = [];
		$tmp = & $tmp[$k];
	}
	$tmp = $value;
	return $data;
}
/**
 * 获取ip
 * @param number $type
 * @return string
 */
function get_ip($type = 0) {
	$type = $type ? 1 : 0;
	static $ip = null;
	if (null !== $ip) return $ip[$type];
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$pos = array_search('unknown', $arr);
		if (false !== $pos) unset($arr[$pos]);
		$ip = trim($arr[0]);
	} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	$long = ip2long($ip);
	$ip = $long ? [$ip, $long] :['0.0.0.0', 0];
	return $ip[$type];
}
/**
 * 清除html代码
 * @param string $string 字符串
 * @return string 返回清除结果
 */
function clear_html($string){
	$string = strip_tags($string);
	$string = trim($string);
	$string = preg_replace('/\t/','',$string);
	$string = preg_replace('/\r\n/','',$string);
	$string = preg_replace('/\r/','',$string);
	$string = preg_replace('/\n/','',$string);
	return trim($string);
}
/**
 * XSS处理script
 * @param string $val
 * @return string 返回清除结果
 */
function remove_xss($val) {
	$val = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $val);
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for ($i = 0; $i < strlen($search); $i++) {
		$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val);
		$val = preg_replace('/(�{0,8}'.ord($search[$i]).';?)/', $search[$i], $val);
	}	
	$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
	$ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	$ra = array_merge($ra1, $ra2);
	$found = true;
	while ($found == true) {
		$val_before = $val;
		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(�{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= '/i';
			$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
			$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
			if ($val_before == $val) {
				$found = false;
			}
		}
	}
	return $val;
}
/**
 * 获取应用版本号
 * @return string 返回应用版本号
 */
function get_ver(){
	return APP_DEBUG? time() : config('app.version');
}
/**
 * 字符串截取
 * @param string $str 字符串
 * @param number $length 截取长度
 * @param number $start 开始位置
 * @param string $suffix 是否显示...
 * @param string $charset 字符集
 * @return string 返回截取结果
 */
function str_substr($str, $length, $start = 0, $suffix = true, $charset = 'utf-8') {
	if(function_exists("mb_substr")) {
		$slice = mb_substr($str, $start, $length, $charset);
	} elseif (function_exists('iconv_substr')) {
		$slice = iconv_substr($str, $start, $length, $charset);
		if(false === $slice) {
			$slice = '';
		}
	} else {
		$re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join('', array_slice($match[0], $start, $length));
	}
	return $suffix ? $slice.'...' : $slice;
}