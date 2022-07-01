<?php
return [		
		'left_delimiter' => '\{', //模板左标识符
		'right_delimiter' => '\}', //模板右标识符
		'prefix' => '.html', //模板文件后缀
		'view_cache' => false, //是否开启模板缓存
		'cache_time' => 10, //缓存时间(0,永久)秒
		'csrf_check' => true, //是否开启csrf表单令牌验证
];