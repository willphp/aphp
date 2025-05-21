## 视图模板

可在对应模板文件 `app/应用/view/控制器/方法.html` 中使用模板标签开发。

### 模板传值

可在控制器中给模板传值，如：

```php
public function index()
{    
    view_with('name', 'aphp'); //返回之前传值
	$list = [
		['id' => 1,'title' => 'php1'],
		['id' => 2,'title' => 'php2'],
	];
	return view()->with('list', $list); //返回时传值
}
// 以下写法传值同上
// return view('', ['name' => 'aphp'])->with(['list' => $list]);
```

### 显示传值

传值后，就可以在模板中使用模板标签来显示，如：

```html
<h1>{$name}</h1>
<ul>
{foreach $list as $vo}
	<li>{$vo.id} - {$vo.title}</li>
{/foreach}
</ul>
```

### 配置标签

所有标签替换规则可在 `config/template.php` 中配置，可自行查看更换。

### 自定标签

在配置文件`config/template.php`中加入自定规则即可，如：

 ```php
 // 在regex_replace中加入
  '/{\s*aphp:\$var\s*}/i' => '<?php echo $\\1.' aphp'?>',
 ```

模板内容：

```html
{php $name='hello'}
自定标签：{aphp:$name}
```

显示效果：

```html
自定标签：hello aphp
```





---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-05-04 09:33:37