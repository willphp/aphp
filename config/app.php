<?php
return [
		'debug' => false, //调试模式
		'trace' => false, //显示调试栏
		'url_rewrite' => false, //url重写(伪静态)
		'key' => 'willphp', //密钥
		'version' => '20220701', //应用版本
		'app_list' => ['home', 'api', 'admin'], //可访问模块
		'validate_dispose' => 'show', //验证响应处理：redirect跳转来源页,show显示Error::validate,不设置返回false
];