<?php
return [
		'page_var' => 'p', //分页$_GET变量
		'page_size' => 10, //每页显示数量
		'page_num' => 5, //页面显示页码数量
		//'page_html' => '%home% %up% %pre% %numlinks% %next% %down% %end%', //显示的html
		'parse_url' => '\willphp\core\Route::pageUrl', //url处理函数或方法
];