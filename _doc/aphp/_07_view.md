## 视图模板

模板文件对应位置：`app/应用/view/控制器/方法.html`

### 赋值渲染

在控制器传递变量到模板并渲染输出：

```php
public function index()
{    
    //view_with('name', 'aphp'); //传值到视图
    $data = ['id' =&gt; 2,'msg' =&gt; 'php'];
    return view()-&gt;with($data); //或return view('', $data)
}
```

### 模板语法

#### 常量输出

| 常量            | 说明          |
| --------------- | ------------- |
| \_\_VERSION\_\_ | 框架版本      |
| \_\_POWERED\_\_ | 框架名+版本号 |
| \_\_WEB\_\_     | 相对URL       |
| \_\_URL\_\_     | 当前URL       |
| \_\_HISTORY\_\_ | 来源URL       |
| \_\_ROOT\_\_    | 根路径        |
| \_\_STATIC\_\_  | 资源目录      |
| \_\_UPLOAD\_\_  | 上传目录      |
| \_\_THEME\_\_   | 当前主题名    |

以上常量可以在模板中直接输出，如：

```
Powered By __POWERED__ 主题:__THEME__
```

#### 包含文件

```
{include file='public/header.html'}
```

#### 模板布局

```
{layout name='layout/layout.html'}
主体内容
```

#### 布局文件

```
{include file='public/header.html'}
{__CONTENT__}
{include file='public/footer.html'}
```

#### 变量赋值

```
{php $hello='hello WillPHP'}
{php $time=time()}
{php $list=db('user')-&gt;where('status',1)-&gt;select()}
```

#### 变量输出

```
{$hello}
{$arr.id} 
{$arr[0]}
{$arr.0.a.b}
{$arr[$cid]}
{$arr[$vo['cid']]}
{$vo.name|default='默认值'} //变量设置默认值 
{session('user.nickname') ?: '游客'} //表量或表达式设置默认
```

#### 变量操作

```
{$id|intval}
{$vo.title|substr=0,10}
{$list-&gt;links()}
```

#### 函数输出

```
{:date('Y-m-d',$vo['ctime'])} 
{:url('abc/abc')}
{:widget('test')-&gt;get()}
```

#### 条件语句

```
{php $id=2}
{if $id==2 ? '2' : '0'} //三元运算
{if $id==1 or $id==2:} id=1|2 {/if}
{if $id==1:} id=1 {else:} id&lt;&gt;1 {/if}
{if $id==1:} id=1 {elseif $id==2:} id=2 {else:} id&lt;&gt;1|2 {/if}
{php $none=''}
{empty $none:}为空{else:}不为空{/empty}
{!empty $none:}不为空{/empty}
```

#### 偱环语句

```
{php $arr=[['id'=&gt;1,'name'=&gt;'a'],['id'=&gt;2,'name'=&gt;'b']]}
{foreach $arr as $v} 
	{$v.id}.{$v.name} |
{/foreach}
{foreach $arr[0] as $k=&gt;$v}
	{$k}.{$v} | 
{/foreach}
```

#### 原样输出

```
{literal}
    {$hello}
{/literal}
__#POWERED#__ //输出：__POWERED__
{#$hello#} //输出：{$hello}
```

### 自定标签

所有标签替换设置在`config/template.php`配置文件中，可自由设置规则替换。

#### 修改标签，如：

```
'regex_literal' =&gt; '/{lite}(.*?){\/lite}/s',
```

修改后效果：

```
{php $hi='hello'}
失效：{literal}{$hi}{/literal}
成功：{lite}{$hi}{/lite}
```

#### 添加标签，如：

```
//在regex_replace配置中加入{aphp:$变量名}
'/{\s*aphp:\$var\s*}/i' =&gt; '&lt;?php echo $\\1.' aphp'?&gt;',
```

添加后效果：

```
{php $hi='hello'}
{aphp:$hi} //显示 hello aphp
```

&gt;注意：在regex_replace中 key var { } 是会自动替换成相应配置

## 开发示例

#### 数据分页

```
获取数据：
{php $list=db('site')-&gt;where('id','&gt;',0)-&gt;order('id DESC')-&gt;paginate(2)}
数据为空：
{empty $list-&gt;toArray():}none{/empty}
偱环输出：
{loop $list as $vo}
    {$vo.id}.{$vo.cname}
{/loop}
分页html：{$list-&gt;links()}
总记录数：{$list-&gt;attr('total')}
当前页码：{$list-&gt;attr('current')}
开始数：{$list-&gt;attr('offset')}
每页记录数：{$list-&gt;attr('page_size')}
总页数：{$list-&gt;attr('page_count')}
```

#### 获取配置

```
{:site('site_title')} 等于 {:config('site.site_title')}
```

#### 生成URL

```
{:url('blog/about')}
```

#### 格式化时间

```
{php $vo['ctime']=time()}
{:date('Y-m-d',$vo['ctime'])} 
{$vo.ctime|date='Y-m-d'} 
```

#### 字符串截取

```
{php $title='hello world'}
{$title|str_substr=0,5}
```

>本文档由 [APHP文档系统](https://doc.aphp.top) 生成，文档更新于：2024-10-25 15:48:59