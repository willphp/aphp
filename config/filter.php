<?php
/**
 * 输入过滤与模板字段处理配置
 */
return [
		//输入过滤配置
		'filter_in' => 'req', //过滤方式req: 过滤绑定参数req		
		'func_html' => 'remove_xss', //html字段处理函数
		'func_except_html' => 'clear_html', //html除外字段处理函数
		//html字段
		'html_fields' => ['phtml'], //html字段列表	
		'html_like' => 'content', //html字段包含
		//指定处理：字段(多个用,分隔) => 函数(多个用数组或用,分隔)
		'field_in' => [				
				'id,p' => 'intval',
				//'pwd' => 'md5', 
		],
		'func_out' => 'stripslashes', //模板输出过滤(全部)
		'func_except_html_out' => 'htmlspecialchars', //模板输出过滤(html除外)
];