## 控制器类

### 示例文件

控制器 `app/index/controller/Index.php`示例文件代码：

```php
namespace app\index\controller;
class Index
{
    public function index()
    {
        echo 'Hello world!'; //打印字符串
    }
    public function hello(string $name = 'php')
    {
        return view()->with('name', $name); //html模板
    }    
    public function api()
    {
        return ['code'=>200, 'msg'=>'ok', 'data'=>['id'=>1]]; //Json数据
    }    
}
```

模板 `app/index/view/index/hello.html`：

```html
<h1>Hello {$name}!</h1>
```

访问 `index/index` 显示： `Hello world!`

访问 `index/hello?name=aphp` 显示： `<h1>Hello aphp!</h1>`

访问 `index/api` 显示：`{"code":200,"msg":"ok","data":{"id":1}}`

### 错误处理

控制器 `app/index/controller/Error.php`：

```php
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
    //自定义404页面
    public function _404(string $msg, array $args = []) {
        dump($msg, $args);
        //return view('public/_404'); //这里可自定义模板
    }
}
```

访问 `abc/abc` 时显示：

```
string(17) "abc/abc 不存在"
array(1) {
  ["path"] => string(7) "abc/abc"
}
```

>更多错误码可查看 config/response.php 中的 code_msg

### 跳转模块

挂载`aphp\core\Jump`模块实现跳转功能：

```php
namespace app\index\controller;
use aphp\core\Jump;
class Index
{
	use Jump;
	public function index()
    {
        return 'Hello world!';
    }
    public function ok()
    {
    	//成功提示(自动判断是否返回Json数据)
        $this->success('Successful', 'index/index');
    }
    //error($msg,$code,$url) //失败提示
	//_jump($info,$status,$url) //合并成功失败提示
	//_msg($msg,$code,$url) //根据code提示
	//_json($code,$msg,$data,$extend) //显示json
	//_url($url,$time) //url跳转
    //isAjax() //是否ajax提交
    //isPost() //是否post提交
    //isGet(): //是否get提交
    //isPut(): //是否put提交
    //isDelete(): //是否delete提交
}
```

访问 `index/ok` 时显示 `Successful` 并自动跳转到 `index/index`

### 请求处理

可使用`input`函数来处理请求参数，如：

```php
input('get.'); //获取$_GET
input('post.'); //获取$_POST
input('get.name'); //不存在返回 ''
input('post.cid', 0, 'intval'); //获取intval($_GET['cid'])
input('id', 1, 'intval'); //获取$_POST['id']或$_GET['id']   
input('get.pwd', '1', ['intval','md5']); //获取md5(intval($_GET['pwd'])) 
```

### 自动过滤

可使用绑定参数`$req`来获取自动过滤的请求参数，如：

```php
namespace app\index\controller;
class Index
{
    public function index(int $id, string $name = 'php', array $req)
    {
        dump($id, $name, $req);
    }
}
```

参数说明：

- 必须参数$id：访问必须设置如?id=1，否则错误416
- 可选参数$name：未设置时默认为php
- 特定参数$req：值为绑定参数，GET，POST三者合并

可在 `config/filter.php` 中设置`$req`过滤：

```php
'auto_filter_req' => true, //自动过滤req参数
'except_key' => [], //排除主键(可写入script脚本)
'auto' => [
    '/^(id|p)$/' => 'intval', //id分页自动转换数字
    '/^content(_\w+|\d+)?$/' => 'remove_xss', //html内容xss过滤
	'pwd' => 'intval|md5', //演示字段md5
	'*' => 'clear_html', //其他处理(必须放在最后)：去除html代码
],
```

请求参数：

```
?id=aa&name=<b>bbb</b>&p=bb
```

过滤结果：

```
int(0) //id
string(10) "<b>bbb</b>"  //name
array(3) {
  ["id"] => int(0)
  ["name"] => string(3) "bbb"
  ["p"] => int(0)
}
```

### 中间件

可在控制器中设置`$middleware`属性来设置方法中间件，如：

```php
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

可在`config/middleware.php`中配置控制器中间件，如：

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

以相似的代码分别建立`Filter.php`，`Auth.php`和`Test.php`中间件：

```php
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

- index/index 显示：-Filter--Auth-index
- index/login 显示：-Filter-login
- index/test 显示：-Filter--Auth--Test-test
