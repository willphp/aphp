## 缓存部件

### 配置

默认`file`缓存，可设置`redis`缓存。配置`config/cache.php`文件：

```php
'driver' => 'redis', //默认驱动(支持file,redis)
'redis' => [
    'host' => '127.0.0.1',
    'port' => 6379,
    'pass' => '123456',
    'database' => 0,
],
```

### 缓存管理

通过`cahce`函数进行设置，检测，获取，删除缓存：

```php
//名称参数：[应用@][路径/]名称
cache('t1', date('Y-m-d H:i:s')); //设置永久
cache('t2', date('Y-m-d H:i:s'), 10); //设置10秒更新
$bool = cache('?test@t1'); //false(检测)
$t1 = cache('t1'); //获取
cache('t1', null); //删除
$t3 = cache_make('t3', fn()=>date('Y-m-d H:i:s'), 20); //获取，不存在则制作
```

### 缓存清理

```php
cache_clear(); //当前应用
cache_clear('*') //所有应用
cache_clear('admint@*'); //指定admin应用
cache_clear('index@widget/tag/*'); //指定部件
```

### 部件生成

命令行生成： `atop make:widget 应用名@部件名 标签`

```shell
php atop make:widget index@test test
```

部件文件 `app/index/widget/Test.php`：

```php
namespace app\index\widget;
use aphp\core\Widget;
class Test extends Widget 
{
	protected string $tag = 'test'; //标签(表名):用于自动更新
	protected int $expire = 0; //更新时间(秒):0永久
    //设置数据(必须)
	public function set($id = '', array $options = [])
	{
		return date('Y-m-d H:i:s');
	}
}
```

### 部件调用

格式：`widget(名称|应用插件@名称)->get([ID参数], [选项参数])`

```php
namespace app\index\controller;
class Index
    public function index()
    {
        //widget('test')->refresh(); //刷新部件
        return widget('test')->get(); //获取缓存时间，刷新后更新        
    }
}
```

### 模板调用

```html
{:widget('test')->get()}
```

### 同步更新

当使用`db`或`model`对表进行更新时，部件表名对应标签(tag)缓存会同步更新。

>本文档由 [APHP文档系统](https://doc.aphp.top) 生成，文档更新于：2024-10-26 14:07:57