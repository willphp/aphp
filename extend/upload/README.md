## 上传

#### 配置

在 `config/upload.php` 中配置，如：

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

#### 控制器API

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

#### 模板使用

使用ajax设置上传接口为 `{:url('api/upload')}?type=avatar`

#### 返回json数据

图片路径：res.data.path
