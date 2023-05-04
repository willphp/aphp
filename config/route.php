<?php
/*----------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: 113344.com
 |----------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2023, 113344.com. All Rights Reserved.
 |---------------------------------------------------------------*/
return [
    'default_controller' => 'index', //默认控制器
    'default_action' => 'index', //默认方法
    'check_regex' => '#^[a-zA-Z0-9\x7f-\xff\%\/\.\-_]+$#', //路由path_info验证正则
    'url_suffix' => '.html', //url函数自动添加后缀
    'clear_suffix' => ['.html'], //路由解析自动清除后缀
    'get_filter_empty' => false, //$_GET变量是否过滤空值和0
    //路由设置正则别名
    'alias' => [
        ':num' => '[0-9\-]+', //数字
        ':float' => '[0-9\.\-]+', //浮点数
        ':string' => '[a-zA-Z0-9\-_]+', //\w
        ':alpha' => '[a-zA-Z\x7f-\xff0-9-_]+', //包含中文
        ':page' => '[0-9]+', //分页数字
        ':any' => '.*', //任意
    ],
];