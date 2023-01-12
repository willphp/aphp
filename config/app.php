<?php
/**
 * 应用基本配置
 */
return [
		'debug' => false, //调试模式
		'trace' => false, //显示调试栏
		'url_rewrite' => true, //url重写(开启伪静态设为true)
		'app_list' => ['index', 'admin', 'api'], //可访问模块(应用)
		'api_list' => ['api'], //Api应用列表
		//'view_path' => ['index' => 'template'], //设置index应用的模板路径为template
		//'theme_on' => ['index'], //设置index应用为多主题
		'theme_get' => 't', //切换主题的$_GET变量设置
		'key' => 'willphp', //加密密钥(可自定义)
		'version' => '20230112', //应用版本号(调用css或js后面加的版本号)
		'validate_dispose' => 'show', //验证处理：show显示Error::validate,redirect跳转来源页,不设置返回false
];