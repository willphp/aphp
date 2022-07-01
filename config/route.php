<?php
return [
		'default_controller' => 'index', //默认控制器
		'default_action' => 'index', //默认方法
		'pathinfo_var' => 's', //pathinfo的$_GET变量
		'validate_get' => '#^[a-zA-Z0-9\x7f-\xff\%\/\.\-_]+$#', //路由$_GET变量验证正则
		'url_suffix' => '.html', //url函数自动添加后缀
		'del_suffix' => ['.html','.php'], //路由解析自动删除后缀	
];