<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
defined('ROOT_PATH') or die('Access Denied');
define("START_MEMORY", memory_get_usage()); // 开始内存占用
define("START_TIME", microtime(true)); // 开始运行时间
const __VERSION__ = 'v5.1.1'; // 版本号
const __POWERED__ = 'APHP ' . __VERSION__; // 版权信息
version_compare(PHP_VERSION, '7.4.3', '<') and die(__POWERED__ . ' requires PHP 7.4.3 or newer.'); // 版本检测
const AT_MUST = 1; // 必须
const AT_NOT_NULL = 2; // 不为空时
const AT_NULL = 3; // 为空时
const AT_SET = 4; // 设置时
const AT_NOT_SET = 5; // 未设置时
const IN_BOTH = 1; // 全部
const IN_INSERT = 2; // 新增时
const IN_UPDATE = 3; // 更新时
const IS_CLI = (PHP_SAPI === 'cli'); // 是否cli模式
define("IS_AJAX", isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'); // 是否ajax请求
define("IS_GET", !IS_CLI && $_SERVER['REQUEST_METHOD'] === 'GET'); // 是否get请求
define("IS_POST", !IS_CLI && $_SERVER['REQUEST_METHOD'] === 'POST'); // 是否post请求
define("IS_PUT", !IS_CLI && ($_SERVER['REQUEST_METHOD'] === 'PUT' || (isset($_POST['_method']) && $_POST['_method'] === 'PUT'))); // 是否put请求
define("IS_DELETE", !IS_CLI && ($_SERVER['REQUEST_METHOD'] === 'DELETE' || (isset($_POST['_method']) && $_POST['_method'] === 'DELETE'))); // 是否delete请求
define("IS_HTTPS", (isset($_SERVER['HTTPS']) && ('1' === $_SERVER['HTTPS'] || 'on' === strtolower($_SERVER['HTTPS']))) || (isset($_SERVER['SERVER_PORT']) && '443' === $_SERVER['SERVER_PORT'])); // 是否https请求
if (is_file(ROOT_PATH . '/vendor/autoload.php')) {
    require ROOT_PATH . '/vendor/autoload.php'; // 使用composer自动加载
} else {
    require ROOT_PATH . '/aphp/autoload.php'; // 使用APHP框架自动加载
}
require ROOT_PATH . '/aphp/helper.php'; // 引入助手函数
if (is_file(ROOT_PATH . '/app/common.php')) {
    require ROOT_PATH . '/app/common.php'; // 引入自定义函数
}