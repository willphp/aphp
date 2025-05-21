## 更新日志

#### APHPv5.3.7 2025-05-21

- 新增：IS_CURL 判断是否为get_curl函数进行请求
- 优化：model()->save()更新时的字段验证
- 优化：路由代码

#### APHPv5.3.6 2025-05-08

- 修复：当直接显示模板时，分页参数不传递的问题

#### APHPv5.3.5 2025-04-27

-  新增：命令模式，形参 -p 传递参数时按顺序填充参数  

#### APHPv5.3.3 2025-04-23

- 优化：错误提示加入debug trace信息
- 优化：默认首页提示信息

#### APHPv5.3.2 2025-04-20

- 优化：str_substr函数截取字符串时先执行clear_html函数
- 优化：文档优化重写

#### APHPv5.3.1 2025-03-05

- 优化：模板标签

#### APHPv5.3.0 2025-02-20

- 优化：日志写入

#### APHPv5.2.9 2025-01-31

- 修复：cli命令调用

#### APHPv5.2.8 2024-12-20

- 兼容：PHP8.4.1
- 修复：若干BUG

#### APHPv5.2.7 2024-11-22

- 模板：支持如：{include file='user/_header.html' title='会员登录'}
- 新增：Tool类dir_copy,dir_move,file_copy,file_move方法
- 优化：主题配置从config/site.php中单独分离成一个文件config/theme.php

#### APHPv5.2.6 2024-10-28

- 优化：自动加载应用函数文件 app/应用名/common.php
- 优化：切换的模板不存在时，则使用default模板

#### APHPv5.2.5 2024-10-24

- 新增：email.smtp扩展
- 新增：扩展调用函数extend()，如：extend('email.smtp')->send(...)
- 新增：db()查询支持cahce($expire=0)缓存，如：db('news')->cahce(10)->find()

#### APHPv5.2.3 2024-10-05

- 模板：支持复杂变量输出，如：{:echo($a\[$b\]\['c'])}  
- 修复：where('')时sql语句拼接错误
- 增强：可设置路由自动重写规则

#### APHPv5.2.2 2024-09-20

- 优化：get_ip函数(去除参数)
- 修复：getSql获取insert语句的BUG
- 新增：where条件支持where('tag_ids', 'find_in_set', 6)
- 增强：分页URL生成可设置不需要保留的get参数
- 增强：路由可设置空控制器空方法

#### APHPv5.2.1 2024-09-10

- 修复：上传saveBase64Image的错误问题
- 新增：验证string -0825
- 优化：make创建表后清除表字段缓存 -0825
- 模板：模板支持{foreach $变量名[键名] as $变量} -0828
- 修复：自动过滤排序字段except_field
- 优化：url生成：url('about/index') about.html

#### APHPv5.2.0 2024-08-19

- 优化：重命名条件常量AT_常量为IF_常量，如：AT_MUST => IF_MUST
- 优化：重命名场景常量IN_常量为AC_常量， 如：IN_UPDAET => AC_UPDATE
- 新增：db()->getColumn('表名.主键=字段@条件')
- 新增：模型属性：string $tag 对应相关widget的tag
- 修复：一些BUG

#### APHPv5.1.6 2024-08-01

- 加强：命令行生成命令：`make`，添加生成表
- 添加：命令行删除命令：`remove`
- 优化：文件上传扩展，重置上传配置
- 修复：where('username|email')存在的BUG
- 模板：添加标签：{!empty $var:}不为空时{/empty}
- 模板：添加{$vo.time|date=Y-m-d} //格式化日期

#### APHPv5.1.5 2024-07-30

- 优化：修改命令行入口`atop`为`aphpcli`
- 加强：命令行命令：`make`，加入模板替换
- 加强：命令行命令：`make`，添加参数`-f`可覆盖生成

#### APHPv5.1.4 2024-07-07

- 新增：模型可验证单个字段值`validateField($field, $value)`
- 修复：未设置操作场景时验证规则不能跳过
- 加强：模型过滤字段
- 加强：`Tool::str_to_array`优先处理`"\n"`-0723
- 新增：where条件支持多字段如：`where('username|email', '1@qq.com')` -0723
- 优化：`Tool::str_to_array` 自动转换`[eq]`为`=`和`[or]`为`|` -0726

#### APHPv5.1.3 2024-07-04

- 去除：表单生成函数form_select和form_radio
- 添加：doc_aphp目录开发文档md文件
- 增强：内置验证规则，详细看 表单验证 文档

#### APHPv5.1.2 2024-06-17

- 新增：表单生成函数form_select和form_radio
- 新增：ids过滤函数ids_filter
- 新增：选项转换数组函数str_to_array
- 新增：curl请求函数get_curl
- 新增：IP获取函数get_ip和get_int_ip

#### APHPv5.1.1 2024-06-04

- 修复`getSql()`转义反斜线
- 优化`column()`返回结果

#### APHPv5.1.0 2024-05-21

- 优化代码
- 修复BUG


---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-05-21 21:54:38