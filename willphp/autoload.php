<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
defined('ROOT_PATH') or die('Access Denied');
class autoload {
	public static function load($class) {
		$file = strtr(ROOT_PATH.'/'.$class.'.php', '\\', '/');
		if (is_file($file)) include $file;
	}
}
spl_autoload_register('autoload::load');