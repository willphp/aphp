## 扩展类库

在 `extend` 目录下可自行扩展功能类库。已有扩展类库可直接调用。

### 图片验证码

在控制器中验证，示例如下：

```php
<?php
// 文件：app/index/controller/Login.php
namespace app\index\controller;
use aphp\core\Jump;
use extend\captcha\Captcha;
class Login
{    
    use Jump;
    public function captcha()
    {
        return (new Captcha())->make();
    } 
    public function login(array $req)
    {
        if ($this->isPost()) {
            $rule = [
                ['captcha', 'captcha', '验证码错误', AT_MUST]
            ];
            validate($rule, $req)->show();
            $this->success('验证码正确');
        }
        return view();
    }
}
```

在模板中调用：

```html
验证码：<input type="text" name="captcha" />
<img src="{:url('login/captcha')}" onclick="this.src='{:url('login/captcha')}?'+Math.random();" style="cursor:pointer;" alt="captcha"/>
```

### 邮件发送

要先在 `config/email.php`配置SMTP服务邮箱后，才能使用。

```php
$smtp = extend('email.smtp');
$r = $smtp->send('邮箱@qq.com', '标题', '内容');
if ($r) {
	echo '发送成功';
} else {
	echo $smtp->error;
}
```

### 生成缩略图

在 `app/common.php` 中加入函数：

```php
function get_thumb(string $image, int $width, int $height, int $thumbType = 6): string
{
    return \extend\thumb\Thumb::init()->getThumb($image, $width, $height, $thumbType);
}
```

模板中使用函数：

```html
<img src="{:get_thumb('./uploads/1.jpg', 200, 100)}" />
```

### 文件上传

可在 `config/upload.php` 中配置上传，图片自动截取指定大小等。

```php
<?php
// 文件：app/index/controller/Api.php
namespace app\index\controller;
use aphp\core\Jump;
use extend\upload\Upload;
class Api
{
    use Jump;    
    //上传接口
    public function upload(string $api = 'avatar')
    {
        $upload = Upload::init($api);
        $res = $upload->save();
        if (!isset($res[0]['path'])) {
            $this->error($upload->getError());
        }
        $this->_json(200, '上传成功', $res[0]);
    }
}
```

在模板文件中，ajax配置上传接口地址：

 ```php
 {:url('api/upload')}?type=avatar
 ```


---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-04-03 21:57:23