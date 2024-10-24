<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
/**
 * 邮件配置
 */
return [
    'smtp_host' => 'smtp.163.com', // smtp服务器
    'smtp_port' => '25', // smtp端口
    'smtp_user' => '@163.com', // smtp邮箱账户
    'smtp_pass' => '', // smtp邮箱授权码
    'smtp_ssl' => '0', // 是否开启ssl
    'send_open' => '0', // 是否开启发送
    'test_send_title' => '测试标题', // 测试标题
    'test_send_content' => '测试内容', // 测试内容
    'test_send_email' => '@qq.com', // 测试发送邮箱
    'send_interval' => '30', // 发送间隔(秒)
];