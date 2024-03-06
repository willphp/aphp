## 验证码

#### 控制器

```php
namespace app\index\controller;
use aphp\core\Jump;
class Login
{    
	use Jump;
    public function captcha()
    {
        return (new \extend\captcha\Captcha())->make();
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

#### 模板

```html
验证码：<input type="text" name="captcha" />
<img src="{:url('login/captcha')}" onclick="this.src='{:url('login/captcha')}?'+Math.random();" style="cursor:pointer;" alt="captcha"/>
```
