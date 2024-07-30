## 表单验证

规则格式：

```
'表单字段', '验证规则[|...]', '错误提示[|...]', '[验证条件]'
```

示例代码：

```php
$rule = [
    ['email', 'email', '邮箱错误', AT_MUST]
];
$data = ['email' => 'abc@163'];
$v=validate($rule, $data);
$v->show(); //失败调用Error::_406
$v->getError(); //失败返回错误，通过返回空数组
$v->isFail(); //失败返回true
```

### 验证条件

```php
const AT_MUST = 1; //必须(默认)
const AT_NOT_NULL = 2; //有值
const AT_NULL = 3; //空值
const AT_SET = 4; //有字段
const AT_NOT_SET = 5; //无字段
```

>验证条件可以填写常量或数字，默认为 AT_MUST

### 内置规则

框架已内置多种验证规则，可直接使用。

#### 必填验证

|  规则 |说明   |
| ------------ | ------------ |
| required | 必填 |
| required_if:field,value | 当field值为value时必填 |
| required_with:field,field... | 任一field有值时必填 |
| required_with_all:field,field... | 所有field有值时必填 |
| required_without:field,field... | 任一field无值时必填 |
| required_without_all:field,field... | 所有field无值时必填 |


#### 格式验证

| 规则 | 说明 |
| ---- | --- |
|alpha|纯字母|
|alpha_num|字母 数字|
|alpha_dash|字母 数字 - _ |
|chs|纯汉字|
|chs_alpha|汉字 字母|
|chs_alpha_num|汉字 字母 数字|
|chs_dash|汉字 字母 数字 - _ |
|number|纯数字(0~n) 不包含负数和小数点|
|int_id|大于0整型(如id,page)|
|regex:[pattern]|正则验证 如：regex:/^\d{5,20}$/|
|float       				|验证浮点数(filter_var验证)|
|int         				|验证数字(filter_var验证)|
|boolean						|可转成布尔值：true false 1 0 '1' '0' |
|url         				|验证url(filter_var验证)|
|email       				|验证邮箱(filter_var验证)|
|ip          				|验证ip(filter_var验证)|
| start\_with:aphp_			|验证前置字符串|
| end\_with:.php				|验证后置字符串|
|contains:aphp				|验证是否包含字符串|
|confirmed:[field]			|必须与另一个字段的值相同|
|different:[field]			|必须与另一个字段的值不同|
|in:1,2,3					|在...之中|
|not_in:1,2					|不在...之中|
|between:1,10				|在n到m之间|
|not_between:1,10			|不在n到m之间|
|length:4,25					|长度设置|
|length:4					|指定长度|
|min:5						|最小值|
|max:25						|最大值|
|eq:值(=:值)				   |等于 如：=:abc|
|eq:_[字段]				   |等于字段，如：eq:_password	|
|neq:10(!=:10)				|不等于|
|gt:10(>:10)					|大于|
|egt:10(>=:10)				|大于等于|
|lt:10(<:10)					|小于|
|elt:10(<=:10)				|小于等于|
|after:2024-10-01			|是否在某个日期之后|
|before:2024-10-01			|是否在某个日期之前|

验证当前操作有效期（注意不是某个值）：

```
expire:2024-01-01,2024-10-01	验证当前操作是否在有效日期
```

图像验证码验证：

```
captcha     验证码验证
```

#### 数据库验证

```
unique:[table.pk,column,where] //唯一验证 
exists:[table.pk,column,where] //存在验证
```

示例：

```
//验证user表条件为status=1和group_id=当前分组ID的username是否存在对应值
exists:user,username,status=1&group_id=_group_id
```

### 特殊规则

```
正则表达式，如：/^\d{5,20}$/    
闭包函数，如：fn($i)=>($i+1)
```

### 内置函数

可使用自定义函数或PHP内置函数进行验证，如：

```
使用PHP内置函数：is_numeric
```

### 自定规则

可以在 `config/validate.php` 配置文件中定义自已的正则验证规则，如： 

```
'username' => '/^\w{4,20}$/', //用户4-20位
'password' => '/^\w{6,12}$/', //密码6-12位
'mobile' => '/^1[3-9]\d{9}$/', //手机号
'qq' => '/^[1-9][0-9]{4,12}$/', //QQ号
'id_card' => '/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/', //身份证号
'bankcard' => '/^[1-9][0-9]{18}$/', //银行卡号
'money' => '/^\d+\.?\d*$/', //金额
```

###  验证示例 

```php
$rule = [
    ['name', 'required|unique:user.id,name', '必须|用户已存在', 2], //有值时
    ['pwd', '/^\w{5,12}$/', '密码5~12位', 2], //有值时
    ['mobile', 'mobile', '手机号错误', AT_MUST], //必须
    ['email', 'email', '邮箱错误', 4], //有字段时
    ['age', fn($val)=>($val>=18 && $val<=60), '年龄18~60'],
];
$data = ['name'=>'', 'pwd'=>'123', 'mobile'=>'x12323332333', 'age'=>12];
//$data['email'] = 'aaa';
$errors = validate($rule, $data, true)->getError();
dump($errors);
```