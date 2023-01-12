<?php
/**
 * 中间件配置
 */
return [		
	//全局中间件
	'global' => [			
		\middleware\Boot::class, //框架启动		
	],			
	//控制器中间件(可自行定义)
	'controller' => [
			'auth' => [
					\middleware\controller\Auth::class, //权限检测
			],
			'test' => [
					\middleware\controller\Test::class, //测试中间件
			],
	],
	//应用中间件(框架内置)
	'web' => [
			'database_query' => [
					\middleware\web\SqlDebug::class, //记录sql到调试栏
			],
			'database_execute' => [
					\middleware\web\SqlDebug::class, //记录sql到调试栏
					\middleware\web\SqlLog::class, //记录sql到日志					
					\middleware\web\CrsfReset::class, //重置表单令牌
			],
			'controller_start' => [
					\middleware\web\CsrfCheck::class, //检测表单令牌
			],
	],
];