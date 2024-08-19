<?php
/*------------------------------------------------------------------
 | 引导文件 2024-08-13 by 无念
 |------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
defined('ROOT_PATH') or die('Access Denied');
define("START_MEMORY", memory_get_usage()); // 开始内存
define("START_TIME", microtime(true)); // 开始时间
const __VERSION__ = 'v5.2.0'; // 版本
const __POWERED__ = 'APHP ' . __VERSION__; // 版权
version_compare(PHP_VERSION, '7.4.3', '<') and die(__POWERED__ . ' requires PHP 7.4.3 or newer.'); // 检测PHP版本
// 条件常量
const IF_MUST = 1;  // 必须
const IF_VALUE = 2; // 有值
const IF_EMPTY = 3; // 空值
const IF_ISSET = 4; // 有字段
const IF_UNSET = 5; // 无字段
// 场景常量
const AC_BOTH = 1;   // 全部操作
const AC_INSERT = 2; // 新增
const AC_UPDATE = 3; // 更新

const IS_CLI = (PHP_SAPI === 'cli'); // 是否cli模式
if (!IS_CLI) {
    define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'); // 是否ajax
    define('IS_GET', $_SERVER['REQUEST_METHOD'] === 'GET'); // 是否get
    define('IS_POST', $_SERVER['REQUEST_METHOD'] === 'POST'); // 是否post
    define('IS_PUT', $_SERVER['REQUEST_METHOD'] === 'PUT' || (isset($_POST['_method']) && $_POST['_method'] === 'PUT')); // 是否put
    define('IS_DELETE', $_SERVER['REQUEST_METHOD'] === 'DELETE' || (isset($_POST['_method']) && $_POST['_method'] === 'DELETE')); // 是否delete
    define('IS_HTTPS', (isset($_SERVER['HTTPS']) && ('1' === $_SERVER['HTTPS'] || 'on' === strtolower($_SERVER['HTTPS']))) || (isset($_SERVER['SERVER_PORT']) && '443' === $_SERVER['SERVER_PORT'])); // 是否https
} else {
    define('IS_AJAX', false); // 是否ajax
    define('IS_GET', false); // 是否get
    define('IS_POST', false); // 是否post
    define('IS_PUT', false); // 是否put
    define('IS_DELETE', false); // 是否delete
    define('IS_HTTPS', false); // 是否https
}
if (is_file(ROOT_PATH . '/vendor/autoload.php')) {
    require ROOT_PATH . '/vendor/autoload.php'; // composer自动加载
} else {
    require ROOT_PATH . '/aphp/autoload.php'; // APHP框架自动加载
}
require ROOT_PATH . '/aphp/helper.php'; // 助手函数
if (is_file(ROOT_PATH . '/app/common.php')) {
    require ROOT_PATH . '/app/common.php'; // 自定义函数
}