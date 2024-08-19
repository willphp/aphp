## 扩展模块 

所有扩展模块位于`extend`目录下，可自行扩展。

### 验证码

控制器代码：

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

模板中调用验证图片：

```html
验证码：<input type="text" name="captcha" />
<img src="{:url('login/captcha')}" onclick="this.src='{:url('login/captcha')}?'+Math.random();" style="cursor:pointer;" alt="captcha"/>
```

### 缩略图

在 `app/common.php` 中加入定义函数：

```php
function get_thumb(string $image, int $width, int $height, int $thumbType = 6): string
{
    return \aphp\core\Thumb::init()->getThumb($image, $width, $height, $thumbType);
}
```

模板中调用函数：

```html
<img src="{:get_thumb('./uploads/1.jpg', 200, 100)}" />
```

### 上传

在`config/upload.php`中配置上传，如：

```php
//头像上传
'avatar' => [
    'allow_ext' => ['jpg', 'jpeg', 'gif', 'png'], //允许扩展名
    'allow_size' => 1048576, //最大上传1MB
    'path' => 'public/uploads/avatar',
    'auto_thumb' => true, //自动生成缩略图
    'thumb' => [
        'thumb_type' => 6, //：1固宽,2固高,3固宽裁高,4固高裁宽,5缩放,6自动裁切
        'max_width' => 0, //当图片宽度超过多少时生成缩略图
        'width' => 100, //缩略图宽度
        'height' => 100, //缩略图高度
        'del_src' => true, //生成缩略图后删除源图片
    ],
],
```

控制器中设置API上传接口：

```php
namespace app\index\controller;
use aphp\core\Jump;
use extend\upload\Upload;
class Api
{
    use Jump;    
    //上传接口
    public function upload(string $type = 'img')
    {
        $upload = Upload::init($type);
        $res = $upload->save();
        if (!isset($res[0]['path'])) {
            $this->error($upload->getError());
        }
        $this->_json(200, '上传成功', $res[0]);
    }
}
```

使用接口处理上传： `{:url('api/upload')}?type=avatar`
