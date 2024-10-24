<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
/**
 * 应用配置
 */
return [
    'app_key' => '4abe69c30e2fa23fc72b031995c9946c', // 应用密钥(加密解密验证)
    'debug' => false, // 调试模式
    'trace' => false, // 调试栏
    'url_rewrite' => true, // url重写(伪静态)
    'log_sql_level' => 0, // 记录SQL级别 0不记录 1只记录execute 2记录全部sql
    'default_timezone' => 'PRC', // 默认时区
    'app_access' => ['index', 'admin', 'api'], // 可访问应用
    'app_api' => ['api'], // Api应用列表
    'app_view_path' => [], // 视图模板路径 应用=>视图路径 如：'index' => 'template'
    'app_multi_theme' => [], // 多主题应用 如：'index'
    'theme_get_var' => '', // 主题切换$_GET变量t 为空关闭切换
    'error_msg' => '页面出错！请稍后再试～', // 关闭Debug后错误提示
];