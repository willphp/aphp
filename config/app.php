<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
return [
    'app_key' => 'b64f03169423386de0b080a248ca3526', //应用密钥(加密解密验证)
    'debug' => false, //调试模式
    'trace' => false, //显示调试栏
    'url_rewrite' => true, //url重写
	'log_sql_level' => 0, //记录sql到日志, 0不记录1只记录execute2记录全部sql
    'default_timezone' => 'PRC', //默认时区
    'app_list' => ['index', 'admin', 'api'], //可访问应用
    'app_api' => ['api'], //Api应用列表
    'view_path' => ['index' => 'template'], //设置模板路径 应用=>根目录下的路径 'index' => 'template'
    'theme_on' => ['index'], // 设置多主题 'index'
    'theme_get' => 't', //主题切换$_GET变量如：t 设为空关闭主题切换
    'error_msg' => '页面出错！请稍后再试～', //关闭Debug错误显示信息
];