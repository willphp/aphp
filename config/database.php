<?php
/**
 * 数据库连接配置(上线配置，本地可以在.env里配置)
 */
return [	
		//默认配置		
		'default' => [
			'db_type' => 'mysql', //数据库驱动类型
			'db_host' => 'localhost', //数据库服务器
			'db_port' => '3306', //服务器端口
			'db_user' => 'root', //数据库用户名
			'db_pwd' => '', //数据库密码
			'db_name' => 'myapp01db', //数据库名
			'db_prefix' => 'wp_', //数据库表前缀
			'db_charset' => 'utf8mb4', //默认字符编码
			'pdo_params' => [
				\PDO::ATTR_CASE => \PDO::CASE_NATURAL,
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
				\PDO::ATTR_STRINGIFY_FETCHES => false,
				\PDO::ATTR_EMULATE_PREPARES => false,
			], //PDO连接参数
		],
		//数据库读服务器
		//'read' => ['db_host' => '127.0.0.1'],
		//使用sqlite数据库
		//'sqlite' => ['dsn' => 'sqlite:'.ROOT_PATH.'/db/mydb.db'],
];