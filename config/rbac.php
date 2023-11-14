<?php
/*----------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: 113344.com
 |----------------------------------------------------------------
 | Author: 大松栩 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2023, 113344.com. All Rights Reserved.
 |---------------------------------------------------------------*/
return [
    'super_uid' => 1, //指定站长ID无需验证int
    'no_auth_controller' => ['index', 'error', 'profile', 'api'],  //无需验证的控制器
    'no_auth_action' => [], //无需验证的方法，格式: 方法名 或 控制器/方法
    'no_auth_prefix' => 'api_', //无需验证的方法前缀
];