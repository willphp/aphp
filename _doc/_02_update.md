## 更新日志

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