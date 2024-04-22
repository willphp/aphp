<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
defined('APHP_TOP') or die('Access Denied');
define('START_MEMORY', memory_get_usage());
define('START_TIME', microtime(true));
const __VERSION__ = 'v5.0.3';
const __POWERED__ = 'APHP ' . __VERSION__;
version_compare(PHP_VERSION, '7.4.3', '<') and die(__POWERED__ . ' requires PHP 7.4.3 or newer.');
const AT_MUST = 1;
const AT_NOT_NULL = 2;
const AT_NULL = 3;
const AT_SET = 4;
const AT_NOT_SET = 5;
const IN_BOTH = 1;
const IN_INSERT = 2;
const IN_UPDATE = 3;
const IS_CLI = (PHP_SAPI === 'cli');
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
define('IS_GET', !IS_CLI && $_SERVER['REQUEST_METHOD'] == 'GET');
define('IS_POST', !IS_CLI && $_SERVER['REQUEST_METHOD'] == 'POST');
define('IS_PUT', !IS_CLI && ($_SERVER['REQUEST_METHOD'] == 'PUT' || (isset($_POST['_method']) && $_POST['_method'] == 'PUT')));
define('IS_DELETE', !IS_CLI && ($_SERVER['REQUEST_METHOD'] == 'DELETE' || (isset($_POST['_method']) && $_POST['_method'] == 'DELETE')));
define('IS_HTTPS', (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) || (isset($_SERVER['SERVER_PORT']) && '443' == $_SERVER['SERVER_PORT']));
if (is_file(APHP_TOP . '/vendor/autoload.php')) {
    require APHP_TOP . '/vendor/autoload.php';
} else {
    require APHP_TOP . '/aphp/autoload.php';
}
require APHP_TOP . '/aphp/helper.php';
if (is_file(APHP_TOP . '/app/common.php')) require APHP_TOP . '/app/common.php';