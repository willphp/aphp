<?php
/**
 * 调试配置
 */
return [
		'level' => [
				'base' => '基本',
				'file' => '文件', //去掉不显示文件加载
				'sql' => 'SQL',
				'debug' => '调试',
				'post' => 'POST',
				'get' => 'GET',
				'cookie' => 'COOKIE',
				'session' => 'SESSION',
				'error' => '错误',
		],
		'trace_show' => true, //是否显示调试栏
		'sql_log' => true, //记录sql到日志
];