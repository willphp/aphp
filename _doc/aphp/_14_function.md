## 函数说明

框架内置多种函数来满足各种开发需求，详情查看 `aphp/helper.php` 文件。

### 开发调试

```php
dump(...$vars); // 打印变量
dd(...$vars); // 打印并结束
dump_const(); // 打印用户常量
log_value($vars); // 记录变量到日志
trace($msg); // 记录变量到调试栏
```

### 功能函数

```php
name_to_snake($name); // 驼峰转下划线
name_to_camel($name); // 下划线转驼峰
str_to_array($str); // 选项转换数组
run_batch_func($value, $func); //对值执行批量函数
encrypt($str, $salt); // 字符串加密
decrypt($str, $salt); // 字符串解密
get_ip(); // 获取ip
get_int_ip(); // 获取int类型ip
clear_html($str); // 清理html代码
remove_xss($str); // 清除xss脚本
get_time_ago(int $time); // 时间多久之前
str_substr($str, 10); // 字符串截取
```