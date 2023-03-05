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
    'debug' => false, //调试模式
    'trace' => false, //显示调试栏
    'url_rewrite' => false, //url重写(开启伪静态设为true)
    'app_list' => ['index', 'admin', 'api'], //可访问模块(应用)
    'api_list' => ['api'], //Api应用列表
    'view_path' => [], //['index' => 'view'] 设置应用=>根目录下的模板路径
    'theme_on' => [], //['index'] 设置index为多主题
];