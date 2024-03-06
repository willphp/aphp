## 缩略图

#### 函数

在 `app/common.php` 中加入函数：

```php
function get_thumb(string $image, int $width, int $height, int $thumbType = 6): string
{
    return \aphp\core\Thumb::init()->getThumb($image, $width, $height, $thumbType);
}
```

#### 模板

```html
<img src="{:get_thumb('./uploads/1.jpg', 200, 100)}" />
```