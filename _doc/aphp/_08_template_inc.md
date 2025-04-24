## 模板共用

通常会把模板公共的地方分离，如头部模板和底部模板，供其他模板使用。

### 头部模板

文件 `app/index/view/public/header.html` 示例：

```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>	
    <title>[title] - 一鱼PHP框架</title>
    <link rel="stylesheet" href="__STATIC__/css/style.css"/>
</head>
<body>
<h1>header</h1>
```
### 底部模板

文件 `app/index/view/public/footer.html` 示例：

```html
<div class="footer">Powered By __POWERED__ 主题:__THEME__</div>
</body>
</html>
```

### 模板包含

文件 `app/index/view/index/index.html` 示例：

```html
{include file='public/header.html' title='首页'}
主体内容
{include file='public/footer.html'}
```

### 布局文件

文件 `app/index/view/layout/layout.html` 示例：

```html
{include file='public/header.html'}
{__CONTENT__}
{include file='public/footer.html'}
```
### 使用布局

文件 `app/index/view/index/index.html` 示例：

```html
{layout name='layout/layout.html'}
主体内容
```

模板中 `主体内容` 会替换布局文件中的  `{__CONTENT__}`


---

**注意：修改共用的模板后不会直接生效，需要清除缓存。**


---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-04-02 22:19:13