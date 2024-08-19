## 上传

#### 配置

在 `config/upload.php` 中配置，如：

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

#### 控制器API

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

#### 模板使用

使用ajax设置上传接口为 `{:url('api/upload')}?api=avatar`

#### 返回json数据

图片路径：res.data.path
