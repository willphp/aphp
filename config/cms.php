<?php
/*------------------------------------------------------------------
 | CMS配置 2024/9/12 0012 by 无念
 |------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
return [
    // 是否为演示模式
    'is_demo' => false, // 开启后无法修改密码，删除表，删除备份
    // 系统表禁删
    'sys_table' => [
        'admin',
        'admin_group',
        'admin_log',
        'attach',
        'auth',
        'auth_node',
        'config',
        'config_group',
        'dict',
        'field',
        'menu',
        'model',
        'model_field',
        'topic',
    ],
    // 默认节点方法
    'default_node_method' => [
        'index' => '列表',
        'add' => '新增',
        'edit' => '修改',
        'del' => '删除',
        'multi' => '修改字段',
    ],
    // 字段类型
    'field_type' => [
        'varchar' => 'varchar', // 默认
        'int' => 'int',
        'tinyint' => 'tinyint',
        'text' => 'text',
        'char' => 'char',
        'smallint' => 'smallint',
        'mediumint' => 'mediumint',
        'bigint' => 'bigint',
        'decimal' => 'decimal',
        'tinytext' => 'tinytext',
        'mediumtext' => 'mediumtext',
        'longtext' => 'longtext',
    ],
    // 字段查询
    'col_search' => [
        'true' => 'true', // 开启(默认)
        'false' => 'false',  // 关闭
        'time' => 'time', // 时间
        'range' => 'range', // 时间范围
        'select' => 'select', // 下拉框
        'between' => 'between', // 数值范围
    ],
    // 查询方式
    'col_search_op' => [
        '=' => '=', // 默认
        'like' => 'like',
        'not like' => 'not like',
        '>' => '>',
        '>=' => '>=',
        '<' => '<',
        '<=' => '<=',
        '<>' => '<>',
        'in' => 'in',
        'not in' => 'not in',
        'between' => 'between',
        'not between' => 'not between',
        'find_in_set' => 'find_in_set',
        'range' => 'range',
        'not range' => 'not range',
        //'null' => 'null',     //未处理
        //'not null' => 'not null', //未处理
    ],
    // 表单类型
    'form_type' => [
        'text' => '文本框', // 默认
        'number' => '数字',
        'datetime' => '日期时间',
        'city' => '省市区',
        'radio' => '单选框',
        'select' => '下拉框',
        'selects' => '多选下拉',
        'textarea' => '文本域',
        'tinymce' => 'tinymce编辑器',
        'switch' => '开关',
        'color' => '取色组件',
        'image' => '图片上传',
        'file' => '文件上传',
    ],
    // layui前端验证
    'lay_verify' => [
        'none' => '无', // 默认
        'required' => '必填',
        'phone' => '手机号',
        'email' => '邮箱',
        'url' => '网址',
        'number' => '数字',
        'date' => '日期',
        'identity' => '身份证',
    ],
    // 条件常量
    'aphp_if' => [
        IF_MUST => '必须',
        IF_VALUE => '有值',
        IF_EMPTY => '空值',
        IF_ISSET => '有字段',
        IF_UNSET => '无字段',
    ],
    // 场景常量
    'aphp_scene' => [
        AC_BOTH => '全部操作',
        AC_INSERT => '新增',
        AC_UPDATE => '更新',
    ],
    // 自动处理方式
    'auto_method' => [
        'string' => '填充字符',
        'field' => '填充字段',
        'function' => '函数处理',
        'method' => '模型方法',
    ],
];