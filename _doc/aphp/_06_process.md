## 开发流程

1. 编写文档：需求分析，明确功能流程和业务逻辑
2. 建数据表：设计需要的数据表
3. 设置配置：配置数据库等相关配置
4. 建立模型：实现数据访问和业务逻辑
5. 建控制器：处理请求逻辑调用模型
6. 建立视图：创建模板界面(API可省略)
7. 运行测试：调试项目

### 编写文档

需求分析，明确功能流程和业务逻辑流程，在每个阶段都有编写文档的必要。

### 建数据表

使用`phpmyadmin`等数据库管理工具操作，示例如下：

```php
-- 创建数据表: aphp_blog --
CREATE TABLE `aphp_blog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='文章表';
```

### 设置配置

在 `config/database.php`文件中修改数据库连接配置，如：

```php
'db_name' => 'www_aphp_top', // 数据库名
'db_user' => 'root', // 数据库用户名
'db_pass' => '123456', // 数据库密码
```

也可以重命名`env.example.env`为`.env`文件，来配置本地环境配置，如：

```php
[APP]
DEBUG = true # 开启调试
TRACE = true # 开启调试栏
URL_REWRITE = true
LOG_SQL_LEVEL = 1 # SQL日等级

[DATABASE]
DEFAULT[DB_DRIVER] = mysql
DEFAULT[DB_HOST] = localhost
DEFAULT[DB_PORT] = 3306
DEFAULT[DB_CHARSET] = utf8mb4
DEFAULT[DB_NAME] = www_aphp_top # 数据库名
DEFAULT[DB_USER] = root # 数据库用户名
DEFAULT[DB_PASS] = 123456 # 数据库密码
DEFAULT[TABLE_PREFIX] = aphp_
```

###  建立模型

可通过命令来生成模型类，如建立表 `blog` 的模型文件：

```php
php aphpcli make:model index@blog id
```

生成的模型文件后，可设置字段自动验证等，如：

```php
<?php
// 文件:app/index/model/Blog.php
namespace app\index\model;
use aphp\core\Model;
class Blog extends Model
{
	protected string $table = 'blog'; // 表名
	protected string $pk = 'id'; // 表主键
	// 自动验证
	protected array $validate = [
        ['title', 'required|unique', '标题必须|标题已存在', IF_MUST, AC_BOTH],
        ['content', 'required', '内容必须', IF_MUST, AC_BOTH],
        ['sort', 'number', '排序必须是数字', IF_MUST, AC_BOTH],
    ];
	// 自动处理
    protected array $auto = [
        ['status', '1', 'string', IF_MUST, AC_INSERT],
    ];
}
```

更多设置和功能请查看 `模型操作` 章节。

### 建控制器

有了模型后，就可以建控制器来处理请求逻辑和调用模型数据，如：

```php
<?php
// 文件：app/index/controller/Blog.php
namespace app\index\controller;
use aphp\core\Jump;
class Blog
{
    use Jump;
    // 分页
    public function index()
    {
        $list = model('blog')->field('content', true)->where('status=1')->order('id DESC')->paginate(2);
        return view()->with(['list' => $list]);
    }
    // 查看
    public function view(int $id)
    {
        $model = model('blog')->find($id);
        $vo = $model->toArray();
        if (!$vo) {
            $this->error('记录不存在');
        }
        return view()->with(['vo' => $vo]);
    }
    // 添加
    public function add(array $req)
    {
        if ($this->isPost()) {
            $r = model('blog')->save($req);
            $this->_jump(['添加成功', '添加失败'], $r, 'index');
        }
        return view();
    }
    // 编辑
    public function edit(int $id, array $req)
    {
        $model = model('blog')->find($id);
        $vo = $model->toArray();
        if (!$vo) {
            $this->error('记录不存在');
        }
        if ($this->isPost()) {
            $r = $model->save($req);
            $this->_jump(['修改成功', '修改失败'], $r, 'index');
        }
        return view()->with(['vo' => $vo]);
    }
    // 删除
    public function del(int $id)
    {
        $blog = model('blog')->find($id);
        $r = $blog->del();
        $this->_jump(['删除成功', '删除失败'], $r, 'index');
    }
}
```

### 建立视图

列表：

```html
<!--app/index/view/blog/index.html-->
<a href="{:url('add')}">添加</a>
<ul>
{foreach $list as $vo}
<li>{$vo.id}.<a href="{:url('view',['id'=>$vo['id']])}">{$vo.title}</a>
- {$vo.update_time|date='Y-m-d H:i:s'} | 
<a href="{:url('edit',['id'=>$vo['id']])}">编辑</a> | 
<a href="{:url('del',['id'=>$vo['id']])}">删除</a>
</li>
{/foreach}
</ul>
{empty $list->toArray():}
暂无记录
{/empty}
```

查看：

```html
<!--app/index/view/blog/view.html-->
标题：{$vo.title}
内容：{$vo.content}
时间：{$vo.update_time|date='Y-m-d'}
```

添加：

```html
<!--app/index/view/blog/add.html-->
<form action="{:url('add')}" method="post">
标题：<input type="text" name="title"/>
内容：<textarea name="content"></textarea>
排序：<input type="text" name="sort" value="100"/>
<input type="submit" value="添加"/>
</form>
```

编辑：

```html
<!--app/index/view/blog/edit.html-->
<form action="{:url('edit')}" method="post">
<input type="hidden" name="id" value="{$vo.id}" />
标题：<input type="text" name="title" value="{$vo.title}"/>
内容：<textarea name="content">{$vo.content}</textarea>
排序：<input type="text" name="sort" value="{$vo.sort}"/>
<input type="submit" value="修改"/>
</form>
```

### 运行测试

通过以上步骤就可以实现对数据表增删改查(`CRUD`) 和`表单自动验证`功能。可在浏览器中访问  `http://绑定域名/blog/index` 测试效果。







---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-04-05 16:39:28