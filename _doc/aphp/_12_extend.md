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
    return \extend\thumb\Thumb::init()->getThumb($image, $width, $height, $thumbType);
}
```

模板中调用函数：

```html
<img src="{:get_thumb('./uploads/1.jpg', 200, 100)}" />
```

### 上传

在`config/upload.php`中配置上传，如：

```php
// 文件类型
'file_type' => [
    'image' => 'jpg|jpeg|gif|png', // 图片
    'zip' => 'zip|rar|7z', // 压缩包
    'doc' => 'doc|ppt|pdf|md|txt|sql', // 文档
    'excel' => 'xls|csv', // 电子表格
    //'audio' => 'mp3|wav', // 音频
    //'video' => 'mp4|avi', // 视频
],
// 上传api类型设置
'api' => [
    // 上传图片
    'image' => [
        'allow_type' => 'image', // 允许类型
        'allow_size' => 2097152, // 最大2MB
        'path' => 'image', // 上传目录
        'image_auto_cut' => true, // 图片自动裁切
        'image_cut' => [
            'type' => 1, // 裁切方式：1固宽,2固高,3固宽裁高,4固高裁宽,5缩放,6自动裁切
            'max_width' => 980, // 最大宽度，超过980时裁成980宽
            'width' => 0, // 裁切宽度
            'height' => 0, // 裁切高度
        ],
    ],
]
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

使用接口处理上传： `{:url('api/upload')}?type=avatar`
