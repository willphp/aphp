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
    'filter_req' => true, //是否开启req过滤
    'func_html' => 'remove_xss', //html字段处理函数
    'func_except_html' => 'clear_html', //html除外字段处理函数
    //例外字段
    'except_field' => ['list_tpl'],
    //html字段
    'html_field' => ['content', 'html'], //html字段列表
    'html_field_like' => 'content', //html字段包含
    //字段自动处理
    'field_auto' => [
        'id,p' => 'intval',
    ],
    //'func_out' => 'stripslashes', //模板输出过滤(全部)
    //'func_out_except_html' => 'htmlspecialchars', //模板输出过滤(html除外)
];