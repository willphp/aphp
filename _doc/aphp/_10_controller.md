## 控制器类

用于处理请求逻辑，调用模型数据。

### 返回示例

```php
<?php
// 文件：app/index/controller/Index.php
namespace app\index\controller;
class Index
{
    public function index()
    {
        return 'Hello world!'; 
    }
    public function hello(string $name = 'php')
    {
        return view()->with('name', $name); //渲染模板
    }    
    public function api()
    {
        return ['code'=>200, 'msg'=>'ok', 'data'=>['id'=>1]]; //数组自动JSON
    }    
}
```

返回数组自动转换为JSON，不用使用json函数转换。

### 错误处理

可根据状态码自定义页面，如：

```php
<?php
// 文件：app/index/controller/Error.php
namespace app\index\controller;
use aphp\core\Jump;
class Error
{
    use Jump;
    //默认处理方法
    public function __call(string $name, array $arguments)
    {
        $msg = $arguments[0] ?? '';
        $code = str_starts_with($name, '_') ? substr($name, 1) : 400;
        $this->error($msg, (int)$code);
    }
    //可自定义404页面
    public function _404(string $msg, array $args = []) {
        dump($msg, $args); // 显示信息 
        //return view('public/_404'); //这里可自定义模板
    }
}
```

### 跳转模块

挂载`aphp\core\Jump`模块可加强功能，如：

```php
<?php
namespace app\index\controller;
use aphp\core\Jump;
class Index
{
    use Jump;
    public function index(int $id = 0)
    {
	    // isAjax isPost isGet isPut isDelete
		if ($this->isAjax()) {
			$this->_json(200, 'Ajax请求', ['id' => $id]);
		}	
		//$this->_url('index/add'); //跳转url(控制器方法)
        return 'Hello world!';
    }
    public function add(int $id = 0)
    {
        if ($id == 1) {
			$this->success('成功', 'index/index');
	    } else {
			$this->error('失败');
		}  
		//$this->_jump(['成功','失败'], $id, 'index/index'); // 同上
    }
}
```

### 参数绑定

控制器方法可设置参数绑定，设置默认值，如：

```php
<?php
namespace app\index\controller;
class Index
{
    public function index(int $id, string $name = 'aphp', array $req)
    {
        dump($id, $name, $req);
    }
}
```

参数说明：

- 必须参数-$id：类型为int，访问必须设置如?id=1，否则跳转_416错误
- 可选参数-$name：类型为string，未设置时默认为aphp
- 特定参数-$req：类型为array，值为绑定参数，GET，POST三者合并

### 参数过滤

当设置参数类型时，会自动把值转换为指定的类型。也可以在配置中设置自动过滤处理，如：

```php
<?php
// 文件：config/filter.php
return [
    'auto_filter_req' => true, // 自动过滤req参数
    'except_field' => ['markdown'], // 排除字段(可写入script脚本)
    'auto' => [
        'markdown' => 'htmlspecialchars', // strip_tags
        '/^(id|p)$/' => 'intval', // id分页自动转换数字
        '/^html(_\w+|\d+)?$/' => 'trim', // html内容
        '/^content(_\w+|\d+)?$/' => 'remove_xss', // 编辑器内容xss过滤
        'pwd' => 'intval|md5', //演示字段md5
        '*' => 'clear_html', // 其他处理(必须放在最后)：去除html代码
    ],
];
```

可自行添加修改，在浏览器中测试过滤处理结果。

### 参数处理

不使用过滤，可使用`input`函数来进行参数处理和设置默认值，如：

```php
input('get.'); //获取$_GET
input('post.'); //获取$_POST
input('get.name'); //不存在返回 ''
input('post.cid', 0, 'intval'); //获取intval($_POST['cid'])
input('id', 1, 'intval'); //获取$_POST['id']或$_GET['id']，默认1 
input('get.pwd', '1', ['intval','md5']); //获取md5(intval($_GET['pwd']))
```

### 中间件

可设置`$middleware`属性来设置方法中间件，如：

```php
<?php
namespace app\index\controller;
class Index
{
    protected array $middleware = [ 
        'common', //所有方法
        'auth' => ['except' => ['login']], //除login外
        'test' => ['only' => ['test']], //仅test 
    ]; 
    public function index() {           
        return 'index';
    }
    public function login() {
        return 'login';
    } 
    public function test() {
        return 'test';
    }  
}
```

在`config/middleware.php`中配置中间件，如：

```php
'controller' => [
    'common' => [
        \middleware\controller\Filter::class, //过滤
    ],
    'auth' => [
        \middleware\controller\Auth::class, //验证
    ],
    'test' => [
        \middleware\controller\Test::class, //测试
    ],
],
```

以相似代码建立`Filter.php`，`Auth.php`和`Test.php`中间件，如：

```php
<?php
// 文件：middleware/controller/Filter.php
namespace middleware\controller;
use Closure;
class Filter
{
    public function run(Closure $next, array $params = []): void
    {
        echo '-Filter-';
        $next();
    }
}
```

访问结果：

```php
index/index 显示：-Filter—Auth-index
index/login 显示：-Filter-login
index/test 显示：-Filter—Auth—Test-test
```


---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-05-04 09:33:01