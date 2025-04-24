## 模型操作

模型用于实现数据访问和业务逻辑，一个数据库表对应一个模型类。

### 基本模型

只需创建如下文件代码，就可进行模型操作：

```php
<?php
// 文件：app/index/model/Blog.php
namespace app\index\model;
use aphp\core\Model;
class Blog extends Model
{
    protected string $table = 'blog'; //表名(要先建表)
    protected string $pk = 'id'; //主键
}
```

### 模型调用 

使用  `model` 函数来对模型进行操作，如：

```
<?php
// 文件：app/index/controller/Index.php
namespace app\index\controller;
class Index
{
    public function index()
    {
		$blog = model('index.blog'); // 跨应用获取
		$blog = model('blog')->find(1); // 获取当前模型对象 
		dump($blog->toArray()); // 对象转换成数组
    }
}
```

注意：模型可用`db`函数的所有方法，不同在于`find`方法返回对象。

### 模型属性

可设置不同的模型属性，属性说明如下：

```php
protected string $dbConfig = ''; //数据库连接配置
protected array $allowFill = ['*']; //允许填充字段
protected array $denyFill = []; //禁止填充字段
//自动写入时间类型：int|date|datetime|timestamp
protected string $autoTimeType = 'int'; //为空时不自动写入
protected string $createTime = 'create_time'; //创建时间字段
protected string $updateTime = 'update_time'; //更新时间字段
protected bool $isBatch = false; //是否批量验证
protected array $validate = []; //自动验证规则
protected array $auto = []; //自动处理规则
protected array $filter = []; //自动字段过滤
protected string $showError = 'show'; //错误响应show|redirect
```

### 常量说明

在设置模型`自动验证`，`自动处理`和`自动过滤`时，需要使用以下常量或值：

```php
//条件常量
const IF_MUST = 1;  //必须
const IF_VALUE = 2; //有值
const IF_EMPTY = 3; //空值
const IF_ISSET = 4; //有字段
const IF_UNSET = 5; //无字段
//场景常量
const AC_BOTH = 1;   //全部操作
const AC_INSERT = 2; //新增
const AC_UPDATE = 3; //更新
```

### 自动验证

可设置自动验证规则，对表单数据提交到数据库时进行验证。如：

```php
//格式:'字段', '验证规则[|...]', '错误提示[|...]', [条件常量], [场景常量]
protected array $validate = [    
    ['username', 'required|unique', '用户必须|用户已存在', IF_MUST, AC_INSERT],
    ['password', '/^\w{6,12}$/', '密码6-12位', IF_MUST, AC_INSERT],  
    ['repassword', 'confirmed:password', '确认密码不一致', IF_MUST, AC_INSERT],    
    ['iq', 'checkIq', 'IQ必须大于100', IF_MUST, AC_BOTH],
    ['email', 'email', '邮箱格式错误', IF_VALUE, AC_BOTH],             
];
//自定义验证规则
public function checkIq($value, string $field, string $params, array $data): bool
{
    return $value > 100;
}
```

验证规则请查看`表单验证`章节，或在模型中自定义验证规则。

### 自动处理

处理方式：

```html
string      //填充(默认)
function    //函数
field       //与其他字段相同
method      //自定义方法
```

处理示例：

```php
//格式:'字段', '处理规则', '处理方式', [条件常量], [场景常量]
protected array $auto = [
    ['password', 'setPwd', 'method', IF_VALUE, AC_BOTH],    
    ['status', '1', 'string', IF_MUST, AC_INSERT],          
    ['add_time', 'time', 'function', 1, 2],     
    ['new_time', 'add_time', 'field', 1, 1],     
];
//自定义处理规则
public function setPwd(string $val, array $data): string
{
    return md5($val);
}
```

### 自动过滤

```php
//格式:'字段', [条件常量], [场景常量]
protected array $filter = [
    ['password', IF_EMPTY, AC_UPDATE], //为空时过滤不更新
];
```

### 字段处理

在从模型获取数据时，可设置某个字段自动处理，如：

```php
    //自动格式化时间(原字段create_time，生成字段_create_time)
    public function getCreateTimeAttr($val, array $data) {
        return date('Y-m-d H:i', $val);
    }
```

### 同步操作

通过前后置方法，在数据新增，修改，删除前后同步执行其他相关操作来满足业务需求，方法如下：

```php
//前置方法
protected function _before_insert(array &$data): void {}
protected function _before_update(array &$data): void {}
protected function _before_delete(array $data): void {}
//后置方法
protected function _after_insert(array $data): void {}
protected function _after_update(array $before, array $after): void {}
protected function _after_delete(array $data): void {}
```

### 同步示例

新增之前验证字段和写入字段，如：

```php
protected function _before_insert(array &$data): void 
{
    //验证数据合法或进行其它操作
    if ($data['cid'] == 0) {
        $this->errors['cid'] = '请选择分类';
        return;
    }
    $data['status'] = 1; //自动写入字段
}
```

删除文章后，删除文章评论，如：

```php
protected function _after_delete(array $data): void 
{
	db('comment')->where('new_id', $data['id'])->delete();
}
```

### 模型新增

```php
// 新增会触发自动验证，自动处理，自动过滤和其前后置方法
$blog = model('blog');
$blog['title'] = 'dog';
$blog['sort'] = 1;
$blog->save(); 
//或:
model('blog')->save(['title'=>'cat', 'sort'=> 2]);
```

### 模型修改

```php
// 修改会触发自动验证，自动处理，自动过滤和其前后置方法
$blog = model('blog')->find(1);
$blog['title'] = 'mao';
$blog['sort'] = 2;
$blog->save();
//或:
model('blog')->save(['id'=>1, 'title'=>'cat', 'sort'=> 3]);
```
### 模型删除

```php
// 删除会触发其前后置方法
$blog = model('blog')->find(1);
$blog->del();
//或:
model('blog')->del(1);
```

### 错误响应

可设置`save`方法验证失败时的响应方式，如：

```php
protected string $showError = 'show'; //默认值：自动跳转Error::_406方法
protected string $showError = 'redirect'; //自动跳转来源页
protected string $showError = ''; //不做跳转
```

当不做跳转时，获取错误：

```php
$blog = model('blog')->find(1);
$blog['cid'] = 0;
$blog->save();
if ($blog->isFail()) {
    dump($blog->getError()); // ['cid'=>'请选择分类']
}
```

### 更多方法

更多方法示例：

```php
$blog = model('blog');
$blog->getTable();  //获取模型表名
$blog->getPk();     //获取表主键
$blog->getPrefix(); //获取表前缀
$blog->where('status', 1)->order('id DESC')->paginate(10); //列表分页
$blog->where('id', 1)->delete(); //删除
// 过滤 字段 => 值, 允许字段, 禁止字段
$blog->filterFieldFill(['user' => 'admin'], ['*'], ['user']); //过滤user
if (empty($data)) {
    $this->error('字段禁止修改');
}
// 验证单个字段
$errors = $blog->validateField('sort', '11a'); //输入非数字
if (!empty($errors)) {
    $this->error(current($errors)); //排序必须为数字
}
```


---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-04-03 17:57:31