## 命令模式

命令行执行优先级：框架命令 》应用命令 》应用控制器方法

### 框架命令

命令帮助：`php aphpcli` 显示如下：

```
|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|
| 1. make:ctrl    [app_name@ctrl_name] [tpl:_def] [-f]                       |
| 2. make:model   [app_name@table_name] [pk] [tpl:_def] [-f]                 |
| 3. make:view    [app_name@ctrl_name] [method] [tpl:_def] [-f]              |
| 4. make:widget  [app_name@widget_name] [tag] [tpl:_def] [-f]               |
| 5. make:command [app_name@command_name] [tpl:_def] [-f]                    |
| 6. make:app     [app_name]                                                 |
| 7. make:table   [app_name@table_name] [tpl:_def] [-f]                      |
|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|
| 0. clear:runtime [app_name(or *)]                                          |
|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|
| 1. remove:ctrl [app_name@ctrl_name]                                        |
| 2. remove:model [app_name@model_name]                                      |
| 3. remove:view [app_name@ctrl_name] [method(or *)]                         |
| 4. remove:widget [app_name@widget_name]                                    |
| 5. remove:command [app_name@command_name]                                  |
| 6. remove:app [app_name]                                                   |
| 7. remove:table [table_name]                                               |
|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|
```

### 生成控制器

命令格式：`php aphpcli make:ctrl 应用名@控制器名 来源模板 -f 覆盖生成`


### 生成模型

命令格式：`php aphpcli make:model 应用名@表名 主键 来源模板 -f 覆盖生成`

### 生成视图模板

命令格式：`php aphpcli make:view 应用名@控制器名 方法名 来源模板 -f 覆盖生成`

### 生成部件

命令格式：`php aphpcli make:widget 应用名@部件名 标签名 来源模板 -f 覆盖生成`

### 生成命令

命令格式：`php aphpcli make:command 应用名@命令名 来源模板 -f 覆盖生成`

### 生成应用

命令格式：`php aphpcli make:app 应用名`

### 生成表

命令格式：`php aphpcli make:table 应用名@表名 来源模板 -f 覆盖生成`

### 清空运行目录

命令格式：`php aphpcli clear:runtime 应用1 应用2 (*全部应用)`

### 删除

命令格式：`php aphpcli remove:类型 参数`

### 来源模板

自定义模板在：`app/应用名/command/make`目录下创建，修改
框架模板在：`aphp/cli/make`目录下

### 模板语法

```
生成时间：{{:date('Y-m-d H:i:s')}} // 函数
控制器：
{{$namespace}} //命名空间(默认)
{{$class}} //类名(默认)
模型：
{{$table_name}} //表名(默认)
{{$pk|default='pk'}} //主键(默认)
自定义：
{{$变量名}}
{{$is_search == '1' ? 'true' : 'false'}} // 根据条件显示
```

### 设置替换数据

控制器： 在`app/应用名/widget/MakeCtrl.php`中设置

模型： 在`app/应用名/widget/MakeModel.php`中设置

视图模板： 在`app/应用名/widget/MakeView.php`中设置

部件： 在`app/应用名/widget/MakeWidget.php`中设置

命令： 在`app/应用名/widget/MakeCommand.php`中设置


### 获取替换数据

如获取`admin`应用`test`控制器`index`模板的替换数据：

```php
$replace = widget('admin.make_view')->set('test', ['tpl' => 'index']);
```

> 执行命令后会自动获取替换数据来替换生成模板中的变量

### 应用命令

生成命令：`php aphpcli make:command index@test`

生成文件：`app/index/command/Test.php`，可获取附加参数，如：

```php
namespace app\index\command;
use aphp\cli\Command;
class Test extends Command
{
	public function cli(array $req = [])
	{
        dump($req);
		echo __METHOD__;
	}
}
```

附加参数可用 `键名:值` 的形式，如执行命令：`php atop test id:1` 显示：

```
array(1) {
  ["id"] => int(1)
}
app\index\command\Test::cli
```

### 控制器方法

```php
namespace app\index\controller;
class Index
{
    public function cli()
    {
        echo __METHOD__;
    }
}
```

执行命令`php aphpcli index@index:cli`结果：`app\index\controller\Index:cli`

### 命令调用 

可用`cli`函数来调用命令，格式为：`cli(命令:方法, [应用])`
