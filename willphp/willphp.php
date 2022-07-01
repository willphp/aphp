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
define('START_MEMORY', memory_get_usage()); //开始内存
define('START_TIME', microtime(true)); //开始时间
const __VERSION__ = 'v3.2.0'; //框架版本
const AT_MUST = 1; //必须
const AT_NOT_NULL = 2; //有值
const AT_NULL = 3; //空值
const AT_SET = 4; //有字段
const AT_NOT_SET = 5; //无字段
const IN_BOTH = 1; //全部
const IN_INSERT = 2; //新增
const IN_UPDATE = 3; //更新
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'); //是否ajax提交
define('IS_GET', $_SERVER['REQUEST_METHOD'] == 'GET'); //是否get提交
define('IS_POST', $_SERVER['REQUEST_METHOD'] == 'POST'); //是否post提交
define('IS_PUT', $_SERVER['REQUEST_METHOD'] == 'PUT' || (isset($_POST['_method']) && $_POST['_method'] == 'PUT')); //是否put提交
define('IS_DELETE', $_SERVER['REQUEST_METHOD'] == 'DELETE' || (isset($_POST['_method']) && $_POST['_method'] == 'DELETE')); //是否delete提交
define('IS_HTTPS', (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) || (isset($_SERVER['SERVER_PORT']) && '443' == $_SERVER['SERVER_PORT']));
define('__ROOT__', rtrim(strtr(dirname($_SERVER['SCRIPT_NAME']), '\\', '/'), '/')); //根目录
define('__STATIC__', __ROOT__.'/static'); //静态资源目录
define('__UPLOAD__', __ROOT__.'/uploads'); //文件上传目录
define('__HOST__', $_SERVER['HTTP_HOST']); //当前主机
define('__HISTORY__', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''); //来源url
if (!session_id()) session_start(); //开启session
if (file_exists(ROOT_PATH.'/vendor/autoload.php')) {
	require ROOT_PATH.'/vendor/autoload.php'; //composer自动加载
} else {
	require ROOT_PATH.'/willphp/autoload.php'; //框架自动加载
}
require ROOT_PATH.'/willphp/helper.php'; //载入助手函数
if (file_exists(ROOT_PATH.'/app/common.php')) require ROOT_PATH.'/app/common.php'; //载入用户函数