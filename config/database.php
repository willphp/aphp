<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
return [
    //默认mysql
    'default' => [
        //'dsn' => 'mysql:host=localhost;port=3306;dbname=myapp01db;charset=utf8mb4', //优先使用dsn
        'db_driver' => 'mysql', //数据库驱动
        'db_host' => 'localhost', //数据库服务器
        'db_port' => '3306', //服务器端口
        'db_name' => 'myapp01db', //数据库名
        'db_charset' => 'utf8mb4', //默认字符编码
        'db_user' => 'root', //数据库用户名
        'db_pass' => '', //数据库密码
        'table_prefix' => 'ap_', //表前缀
        'pdo_params' => [
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
        ], //PDO连接参数
    ],
];