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
        return (new \extend\captcha\Captcha())-&gt;make();
    }    
    public function login(array $req)
    {
		if ($this-&gt;isPost()) {
            $rule = [
                ['captcha', 'captcha', '验证码错误', AT_MUST]
            ];
            validate($rule, $req)-&gt;show();
            $this-&gt;success('验证码正确');
        }
        return view();
    }
}
```

模板中调用验证图片：

```html
验证码：&lt;input type=&quot;text&quot; name=&quot;captcha&quot; /&gt;
&lt;img src=&quot;{:url('login/captcha')}&quot; onclick=&quot;this.src='{:url('login/captcha')}?'+Math.random();&quot; style=&quot;cursor:pointer;&quot; alt=&quot;captcha&quot;/&gt;
```

### 缩略图

在 `app/common.php` 中加入定义函数：

```php
function get_thumb(string $image, int $width, int $height, int $thumbType = 6): string
{
    return \extend\thumb\Thumb::init()-&gt;getThumb($image, $width, $height, $thumbType);
}
```

模板中调用函数：

```html
&lt;img src=&quot;{:get_thumb('./uploads/1.jpg', 200, 100)}&quot; /&gt;
```

### 上传

在`config/upload.php`中配置上传，如：

```php
// 文件类型
'file_type' =&gt; [
    'image' =&gt; 'jpg|jpeg|gif|png', // 图片
    'zip' =&gt; 'zip|rar|7z', // 压缩包
    'doc' =&gt; 'doc|ppt|pdf|md|txt|sql', // 文档
    'excel' =&gt; 'xls|csv', // 电子表格
    //'audio' =&gt; 'mp3|wav', // 音频
    //'video' =&gt; 'mp4|avi', // 视频
],
// 上传api类型设置
'api' =&gt; [
    // 上传图片
    'image' =&gt; [
        'allow_type' =&gt; 'image', // 允许类型
        'allow_size' =&gt; 2097152, // 最大2MB
        'path' =&gt; 'image', // 上传目录
        'image_auto_cut' =&gt; true, // 图片自动裁切
        'image_cut' =&gt; [
            'type' =&gt; 1, // 裁切方式：1固宽,2固高,3固宽裁高,4固高裁宽,5缩放,6自动裁切
            'max_width' =&gt; 980, // 最大宽度，超过980时裁成980宽
            'width' =&gt; 0, // 裁切宽度
            'height' =&gt; 0, // 裁切高度
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
        $res = $upload-&gt;save();
        if (!isset($res[0]['path'])) {
            $this-&gt;error($upload-&gt;getError());
        }
        $this-&gt;_json(200, '上传成功', $res[0]);
    }
}
```

使用接口处理上传： `{:url('api/upload')}?type=avatar`

### 邮件发送

```php
$smtp = extend('email.smtp');
$r = $smtp-&gt;send('邮箱@qq.com', '标题', '内容');
if ($r) {
	echo '发送成功';
} else {
	echo $smtp-&gt;error;
}
```

>本文档由 [APHP文档系统](https://doc.aphp.top) 生成，文档更新于：2024-10-25 15:52:25