## 模型操作

在`app/应用名/model`中定义模型类，如：

```php
namespace app\index\model;
use aphp\core\Model;
class Cate extends Model
{
    protected string $table = 'cate'; //表名
    protected string $pk = 'id'; //主键
    
    //自动格式化(原字段create_time，生成字段_create_time)
    public function getCreateTimeAttr($val, array $data) {
        return date('Y-m-d H:i', $val);
    }
}
```

### 获取模型

```php
$cate = model('index.cate'); //跨应用获取
$cate = model('cate')->find(1); //获取当前模型对象 
dump($cate->toArray()); //对象转换成数组 
```

### 模型属性

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

### 前后方法

可在模型新增，模型修改，模型删除前后设置方法：

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

使用示例：

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

验证规则设置同`表单验证`，附加`场景常量`参数，示例如下：

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

### 自动处理

处理方式：

```
string      //填充(默认)
function    //函数
field		//与其他字段相同
method      //自定义方法
```

示例代码：

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

### 模型使用

模型在新增和修改数据时会触发`自动验证`，`自动处理`和`自动过滤`。

### 模型新增

```php
$cate = model('cate');
$cate['cname'] = 'cat';
$cate['sort'] = 1;
$cate->save();

//或:
model('cate')->save(['cname'=>'cat', 'sort'=> 2]);
```

### 模型修改

```php
$cate = model('cate')->find(1);
$cate['cname'] = 'dog';
$cate['sort'] = 2;
$cate->save();

//或:
model('cate')->save(['id'=>1, 'cname'=>'cat', 'sort'=> 2]);
```

### 模型删除

```php
$cate = model('cate')->find(1);
$cate->del();
//或:
model('cate')->del(1);
```

### 错误响应

可设置save方法自动验证失败的默认响应方式，如：

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

### 其它方法

可使用使用`数据库表`中的所有方法，如：

```php
$cate = model('cate');
$cate->getTable();  //获取模型表名
$cate->getPk();     //获取表主键
$cate->getPrefix(); //获取表前缀
$cate->where('status', 1)->order('id DESC')->paginate(10); //列表分页
$cate->where('id', 1)->delete(); //删除
// 过滤 字段 => 值, 允许字段, 禁止字段
$cate->filterFieldFill(['user' => 'admin'], ['*'], ['user']); //过滤
if (empty($data)) {
    $this->error('字段禁止修改');
}
// 验证单个字段
$errors = $cate->validateField('sort', '11a');
if (!empty($errors)) {
    $this->error(current($errors)); //排序必须为数字
}
```

>本文档由 [APHP文档系统](https://doc.aphp.top) 生成，文档更新于：2024-10-26 14:08:18