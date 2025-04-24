##  模板标签

可在模板中直接显示特定的常量或显示从控制器中传递过来的变量。

### 常量输出

| 常量 | 说明 |
| ------ | ------ |
| \_\_VERSION\_\_ | 框架版本 |
| \_\_POWERED\_\_ | 框架名+版本号 |
| \_\_WEB\_\_| 相对URL|
| \_\_URL\_\_| 当前URL|
| \_\_HISTORY\_\_| 来源URL|
| \_\_ROOT\_\_| 根路径|
| \_\_STATIC\_\_| 资源目录|
| \_\_UPLOAD\_\_| 上传目录|
| \_\_THEME\_\_| 当前主题名|

以上常量可以在模板中直接输出，如：

```php
Powered By __POWERED__ 主题:__THEME__
```

### 变量输出

可输出从控制器中传值过来的变量，如：

```php
{$name} 
{$arr.id} // 数组
{$arr[0]} 
{$arr.0.a.b}
{$arr[$cid]}
{$arr[$vo['cid']]}
{$vo.name|default='默认值'} //设置默认值 
{$id|intval} // 函数处理
{$vo.title|substr=0,10} // 截取
{$list->links()} // 对象方法
```

### 变量创建

可创建变量，赋值后输出，如：

```php 
{php $hello='hello aphp'}
{php $time=time()}
说：{$hello} 时间：{$time|date='Y-m-d H:i'}
{php $list=db('blog')->where('status',1)->select()}
{foreach $list as $vo}
	{$vo.id} - {$vo.title} |
{/foreach}
```

### 函数输出

可在模板中输出函数结果，支持自定义函数，如：

```php
{:date('Y-m-d',$vo['ctime'])} 
{:url('abc/abc')}
{:widget('test')->get()}
```

### 原样输出

可用 `{literal}` 标签来原样输出，如：

```php
{literal}
    {$hello}
{/literal}
__#POWERED#__ //输出：__POWERED__
{#$hello#} //输出：{$hello}
```

### 条件语句

```php
{php $id=2}
{if $id==2 ? '2' : '0'} //三元运算
{if $id==1 || $id==2:} id=1|2 {/if}
{if $id==1:} id=1 {else:} id<>1 {/if}
{if $id==1:} id=1 {elseif $id==2:} id=2 {else:} id<>1|2 {/if}
{php $none=''}
{empty $none:}为空{else:}不为空{/empty}
{!empty $none:}不为空{/empty}
{if 条件 ? '值'}
```

### 偱环语句

```php
{php $arr=[['id'=>1,'name'=>'a'],['id'=>2,'name'=>'b']]}
{foreach $arr as $v} 
    {$v.id}.{$v.name} |
{/foreach}
{foreach $arr[0] as $k=>$v}
    {$k}.{$v} | 
{/foreach}
```

### 常用标签

数据分页：

```php
获取数据：
{php $list=db('site')->where('id','>',0)->order('id DESC')->paginate(2)}
数据为空：
{empty $list->toArray():}none{/empty}
偱环输出：
{loop $list as $vo}
    {$vo.id}.{$vo.cname}
{/loop}
分页html：{$list->links()}
总记录数：{$list->attr('total')}
当前页码：{$list->attr('current')}
开始数：{$list->attr('offset')}
每页记录数：{$list->attr('page_size')}
总页数：{$list->attr('page_count')}
```

配置获取：

```php
{:site('site_title')} 等同 {:config('site.site_title')}
```

生成URL：

```php
{:url('blog/about')}
```

格式化日期时间：

```php
{php $vo['ctime']=time()}
{:date('Y-m-d',$vo['ctime'])} 
{$vo.ctime|date='Y-m-d'}
```

字符串截取:：

```php
{php $title='hello world'}
{$title|str_substr=0,5}
{$title|str_substr=3}
```



---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-04-10 11:01:29