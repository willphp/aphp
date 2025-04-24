## 缓存部件

默认使用 `file` 缓存，可设置 `redis` 缓存，如：

```php
// 文件：config/cache.php
return [
    'driver' => 'redis', // 改为redis
    'redis' => [
        'host' => '127.0.0.1', // 地址
        'port' => 6379, // 端口
        'pass' => '123456', // 密码
        'database' => 0, // 数据序号
    ],
];
```

### 缓存管理

用`cahce`函数进行设置，检测，获取，删除缓存，如：

```php
//名称参数：[应用@][路径/]名称
cache('t1', date('Y-m-d H:i:s')); // 设置永久
cache('t2', date('Y-m-d H:i:s'), 10); // 设置10秒更新
$bool = cache('?test@t1'); // 检测(false)
$t1 = cache('t1'); // 获取
cache('t1', null); // 删除
$t3 = cache_make('t3', fn()=>date('Y-m-d H:i:s'), 20); // 获取，不存在则建立
```

### 缓存清理

```php
cache_clear();   // 当前应用
cache_clear('*') // 所有应用
cache_clear('admint@*'); // 指定admin应用
cache_clear('index@widget/tag/*'); // 指定部件
```

### 数据库缓存

使用 `db` 函数添加 `cache` 方法可缓存从数据库获取的数据，如：

```php
$cache = db('blog')->where('id=1')->cache(0)->find(); // 可设置缓存时间(秒)
```

### 部件缓存

从数据库读取数据后，需要处理再存入缓存，可以使用部件`widget`类来实现，示例如下：

```php
<?php
// 文件：app/index/widget/Test.php
namespace app\index\widget;
use aphp\core\Widget;
class Test extends Widget 
{
	protected string $tag = 'test'; //标签(表名):用于自动更新
	protected int $expire = 0; //缓存时间(秒):0永久
    //设置数据(必须)
	public function set($id = '', array $options = [])
	{
		// 此处从数据库获取数据并处理...
		$data = date('Y-m-d H:i:s'); // 测试用
		return $data;
	}
}
```

### 部件调用

格式：`widget(名称|应用@名称)->get([ID参数], [选项参数])`

```php
<?php
namespace app\index\controller;
class Index
    public function index()
    {
        //widget('test')->reload(); // 部件重载
        return widget('test')->get(); // 获取，刷新后更新        
    }
}
```

模板调用部件：

```html
{:widget('test')->get()}
```

### 部件更新

当使用 `db` 或 `model` 对表进行更新时，部件表名对应标签 `tag` 缓存会同步更新。


---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-04-03 21:28:18