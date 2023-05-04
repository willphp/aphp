<?php
/*----------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: 113344.com
 |----------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2023, 113344.com. All Rights Reserved.
 |---------------------------------------------------------------*/
declare(strict_types=1);
define('START_MEMORY', memory_get_usage());
define('START_TIME', microtime(true));
const __VERSION__ = 'v4.2.0';
const __POWERED__ = 'WillPHP' . __VERSION__;
version_compare(PHP_VERSION, '7.4.3', '<') and die(__POWERED__ . ' requires PHP 7.4.3 or newer.');
if (PHP_SAPI != 'cli' && !isset($_SERVER['PATH_INFO'])) {
    die('The server does not support PHPINFO mode.');
}
const AT_MUST = 1; //必须
const AT_NOT_NULL = 2; //有值
const AT_NULL = 3; //空值
const AT_SET = 4; //有字段
const AT_NOT_SET = 5; //无字段
const IN_BOTH = 1; //全部
const IN_INSERT = 2; //新增
const IN_UPDATE = 3; //更新
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
define('IS_GET', PHP_SAPI != 'cli' && $_SERVER['REQUEST_METHOD'] == 'GET');
define('IS_POST', PHP_SAPI != 'cli' && $_SERVER['REQUEST_METHOD'] == 'POST');
define('IS_PUT', PHP_SAPI != 'cli' && ($_SERVER['REQUEST_METHOD'] == 'PUT' || (isset($_POST['_method']) && $_POST['_method'] == 'PUT')));
define('IS_DELETE', PHP_SAPI != 'cli' && ($_SERVER['REQUEST_METHOD'] == 'DELETE' || (isset($_POST['_method']) && $_POST['_method'] == 'DELETE')));
define('IS_HTTPS', (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) || (isset($_SERVER['SERVER_PORT']) && '443' == $_SERVER['SERVER_PORT']));
if (is_file(ROOT_PATH . '/vendor/autoload.php')) {
    require ROOT_PATH . '/vendor/autoload.php';
} else {
    require ROOT_PATH . '/willphp/autoload.php';
}
require ROOT_PATH . '/willphp/helper.php';
if (is_file(ROOT_PATH . '/app/common.php')) {
    require ROOT_PATH . '/app/common.php';
}