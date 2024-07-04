## 命令模式

命令行执行优先级：框架命令 》应用命令 》应用控制器方法

### 框架命令

| 框架命令          | 格式和参数 |
|-----------------|---------|
| 生成应用 | make:app -[app]                           |
| 生成控制器 | make:ctrl    -[app@ctrl] -[tpl] |
| 生成模型   | make:model   -[app@table] -[pk] -[tpl] |
| 生成模板   | make:view    -[app@ctrl] -[method] -[tpl] |
| 生成部件   | make:widget  -[app@name] -[tag] -[tpl] |
| 生成命令   | make:command -[app@name] -[tpl] |
| 清除缓冲 | clear:runtime      -[app(\*)] |

生成模板tpl位于 `aphp/cli/make`目录下，可自行修改。

### 应用命令

生成应用命令：

```shell
php atop make:command index@test
```

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

执行命令`php atop index@index:cli`结果：`app\index\controller\Index:cli`

### 命令调用 

可用`cli`函数来调用命令，格式为：`cli(命令:方法, [应用])`
