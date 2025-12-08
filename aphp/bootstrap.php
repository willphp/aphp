<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
defined('ROOT_PATH') || exit('Access Denied'); // 检测常量
version_compare(PHP_VERSION, '8.1', '<') && exit('Requires PHP 8.1 or newer.'); // 检测PHP版本
const IS_CLI = (PHP_SAPI === 'cli'); // 是否cli模式
// 处理跨域预检请求
if (!IS_CLI && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *'); // 允许源域名
    header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization'); // 允许请求头
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'); // 允许请求类型
    exit();
}
const __VERSION__ = '6.0.1'; // 框架版本号
const __POWERED__ = 'APHP_v' . __VERSION__; // 框架全称
// 字段验证(Field Validate)条件常量
const FV_MUST = 1;  // 必须
const FV_VALUE = 2; // 有值
const FV_EMPTY = 3; // 空值
const FV_ISSET = 4; // 有字段
const FV_UNSET = 5; // 无字段
// 模型操作(Action)场景常量
const AC_BOTH = 1;   // 全部操作
const AC_INSERT = 2; // 新增
const AC_UPDATE = 3; // 更新
define('START_MEMORY', memory_get_usage()); // 开始内存
define('START_TIME', microtime(true)); // 开始时间
define('IS_AJAX', !IS_CLI && (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')); // 是否ajax
define('IS_GET', !IS_CLI && $_SERVER['REQUEST_METHOD'] === 'GET'); // 是否get
define('IS_POST', !IS_CLI && $_SERVER['REQUEST_METHOD'] === 'POST'); // 是否post
define('IS_PUT', !IS_CLI && ($_SERVER['REQUEST_METHOD'] === 'PUT' || (isset($_POST['_method']) && $_POST['_method'] === 'PUT'))); // 是否put
define('IS_DELETE', !IS_CLI && ($_SERVER['REQUEST_METHOD'] === 'DELETE' || (isset($_POST['_method']) && $_POST['_method'] === 'DELETE'))); // 是否delete
define('IS_HTTPS', !IS_CLI && ((isset($_SERVER['HTTPS']) && ('1' === $_SERVER['HTTPS'] || 'on' === strtolower($_SERVER['HTTPS']))) || (isset($_SERVER['SERVER_PORT']) && '443' === $_SERVER['SERVER_PORT']))); // 是否https
define('IS_CURL', !IS_CLI && isset($_SERVER['HTTP_X_API_CLIENT']) && $_SERVER['HTTP_X_API_CLIENT'] === 'curl'); // 是否为curl请求(get_curl函数)
if (is_file(ROOT_PATH . '/vendor/autoload.php')) {
    require ROOT_PATH . '/vendor/autoload.php'; // composer自动加载
} else {
    // 框架自动加载器
    spl_autoload_register(function (string $class) {
        $file = strtr(ROOT_PATH . '/' . $class . '.php', '\\', '/');
        if (is_file($file)) {
            include $file; // 类自动加载
        }
    });
}
require ROOT_PATH . '/aphp/function.php'; // 框架核心函数
require ROOT_PATH . '/aphp/helper.php'; // 助手函数
is_file(ROOT_PATH . '/app/common.php') && require ROOT_PATH . '/app/common.php'; // 公共自定义函数