## 命令模式

在根目录下执行 `php aphpcli` 加参数来实现生成文件，调用方法等操作。

### 命令分类

命令执行优先级：内置命令 》 应用命令 》控制器方法

### 内置命令

执行`php aphpcli`显示所有内置命令：

```shell
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

### 参数说明

- [app_name@xxx_name]： 应用名@生成名
- [tpl]：来源模板(默认`_def`可自定义)
- [-f]：-f 覆盖生成
- [pk]：表主键
- [method]：方法

### 来源模板

- 内置模板：`aphp/cli/make` 目录下查看
- 自定模板：`app/应用名/command/make` 目录下创建

### 模板语法

```php
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

### 变量替换

自定义控制器生成的默认模板，如：

```php
<?php
// 文件：app/index/command/make/controller/_def.tpl
namespace {{$namespace}}\controller;
class {{$class}}
{
    protected string $name = '{{$table}}';
    public function index()
    {
        return $this->name;
    }
}
```

需要替换自定义变量`{{$table}}`，可通过建立相关生成部件来设置数据。如：

```php
<?php
// 文件：app/index/widget/MakeCtrl.php
namespace app\index\widget;
use aphp\core\Widget;
class MakeCtrl extends Widget
{
    protected string $tag = 'model';
    protected int $expire = 0;
	//$id 是生成的名称，当生成模板时，$options 会传 ['tpl' => `方法名`]
    public function set($id = '', array $options = []): array
    {
		$data = [
			'table' => 'aphp_'.$id
		];
        return $data;
    }
}
```

执行命令：

```php
php aphpcli make:ctrl index@test _def -f
```

生成的控制器：

```php
<?php
// 文件：app/index/controller/Test.php
namespace app\index\controller;
class Test
{
    protected string $name = 'aphp_test';
    public function index()
    {
        return $this->name;
    }
}
```

### 应用命令

 可用内置命令生成应用命令，如：
 
 ```php
 php aphpcli make:command index@test
 ```

可在生成的命令文件中，附加参数，如：

```php
<?php
// 文件：app/index/command/Test.php
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

附加参数可用 `键名:值` 的形式，如执行命令：`php aphpcli test id:1` ：

```php
array(1) {
  ["id"] => int(1)
}
app\index\command\Test::cli
```

### 执行控制器方法

在控制器中添加如下方法：

```php
<?php
namespace app\index\controller;
class Index
{
    public function cli()
    {
        echo __METHOD__;
    }
}
```

执行命令 `php aphpcli index@index:cli` 结果：

```php
app\index\controller\Index:cli
```

### 命令函数

可用`cli`函数来调用命令，格式为：

```php
cli(命令:方法, [应用])
```



---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-04-04 11:51:26