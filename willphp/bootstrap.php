<?php
/*----------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: 113344.com
 |----------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2023, 113344.com. All Rights Reserved.
 |---------------------------------------------------------------*/
declare(strict_types=1); //采用严格模式
define('START_MEMORY', memory_get_usage()); //开始时间
define('START_TIME', microtime(true)); //开始内存
const __VERSION__ = 'v4.6.6'; //框架版本
const __POWERED__ = 'WillPHP ' . __VERSION__; //框架全称
version_compare(PHP_VERSION, '7.4.3', '<') and die(__POWERED__ . ' 仅支持 PHP 7.4.3+ 以上版本！'); //PHP版本验证
//条件常量，用于字段验证，处理，过滤
const AT_MUST = 1; //必须
const AT_NOT_NULL = 2; //有值
const AT_NULL = 3; //空值
const AT_SET = 4; //有字段
const AT_NOT_SET = 5; //无字段
//时机常量，用于模型验证，处理，过滤
const IN_BOTH = 1; //全部
const IN_INSERT = 2; //新增
const IN_UPDATE = 3; //更新
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'); //ajax提交
define('IS_GET', PHP_SAPI != 'cli' && $_SERVER['REQUEST_METHOD'] == 'GET'); //get提交
define('IS_POST', PHP_SAPI != 'cli' && $_SERVER['REQUEST_METHOD'] == 'POST'); //post提交
define('IS_PUT', PHP_SAPI != 'cli' && ($_SERVER['REQUEST_METHOD'] == 'PUT' || (isset($_POST['_method']) && $_POST['_method'] == 'PUT'))); //put提交
define('IS_DELETE', PHP_SAPI != 'cli' && ($_SERVER['REQUEST_METHOD'] == 'DELETE' || (isset($_POST['_method']) && $_POST['_method'] == 'DELETE'))); //delete提交
define('IS_HTTPS', (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) || (isset($_SERVER['SERVER_PORT']) && '443' == $_SERVER['SERVER_PORT'])); //https协议
//类自动加载方式
if (is_file(ROOT_PATH . '/vendor/autoload.php')) {
    require ROOT_PATH . '/vendor/autoload.php'; //composer自动加载
} else {
    require ROOT_PATH . '/willphp/autoload.php'; //框架自动加载(无须composer类库)
}
require ROOT_PATH . '/willphp/helper.php'; //载入助手函数库
if (is_file(ROOT_PATH . '/app/common.php')) require ROOT_PATH . '/app/common.php'; //载入自定义函数库