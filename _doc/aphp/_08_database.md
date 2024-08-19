## 数据操作

三层数据库操作逐步加强：连接对象(pdo) 《 查询构造(db)《 模型(model)

### 连接对象

使用`pdo`函数可获取数据库连接对象进行操作，方法如下：

```php
//getConfig 获取配置
//query 执行sql查询
//execute 执行sql操作
//getNumRows 响应条数
//getInsertId 最后插入ID
//getRealSql 真实SQL
//trans 事务闭包
//startTrans 开启事务
//commit 提交事务
//rollback 回滚事务
$d = pdo()->query('select * from wp_test where id=:id', ['id'=>1]); //同id=?', [1]
dump($d);
```

### 查询构造

在连接对象的基础上使用`db`函数进一步加强数据库操作。

获取连接对象：

```php
db('blog'); //单表 (注意：表名不含前缀)
db()->table('blog,cates'); //多表
db('blog', 'read'); //使用read配置
db('blog', ['db_name'=>'db03']); //连接db03数据库
```

设置别名： 

```php
db('blog')->alias('a'); 
db()->table('blog a');
db()->table('blog a,cate b');
```

设置字段：

```php
db('blog')->field('id,title'); //支持数组  
db('blog')->field('content', true); //字段排除  
db()->table('blog a,cate b')->field('a.*,b.cname');
```

设置条件：

```php
db('blog')->where(['id'=>1,'status'=>1]); //'id=1 AND status=1'
db('blog')->where('id', '=', 1, 'or')->where('id, 2); //id=1 OR id=2 
```

设置排序：

```php
db('blog')->order('id ASC,cid DESC'); //支持数组['id'=>'asc','cid'=>'desc']
db('blog')->order('id','desc');
```

查询条数：

```php
db('blog')->limit(10);
db('blog')->limit(0,10); //同'0,10'
```

单行查询：

```php
$blog = db('blog');
$blog->where('id', 1)->find();
$blog->find(1); //同上
$blog->getById(1); //同上
$blog->where('id', 1)->value('title'); //返回title的值
```

> 查询单行会自动增加 `limit(1)`

多行查询：

```php
db('blog')->order('id DESC')->limit(10)->select(); //二维数组
db('cate')->column('cname', 'id'); //[id=>cname]一维数组
db('cate')->column('id,cname'); //返回二维数组
db('cate')->column('*', 'id'); //返回二维数组
db()->getColumn('admin.id=username@status=1'); //表名.主键=字段@条件
```

多行分页：

```php
$list = db('blog')->order('id DESC')->paginate(5); //每页5条
foreach ($list as $vo) {
    echo '<p>'.$vo['id'].'</p>';
}
echo $list->pageLink(); //分页html 
```

分页查询：

```php
$p = 1; //当前页数，从1开始
if (isset($_GET['p'])) {
    $p = max(1, intval($_GET['p']));
}
db('blog')->page($p, 10)->select(); //每页10条
```

聚合查询：

```php
db('blog')->where('cid', 1)->count(); //总记录数   
db('blog')->sum('hits'); //总和
db('blog')->min('hits'); //最小值
db('blog')->max('hits'); //最大值
db('blog')->avg('hits'); //平均值
```

关联查询：

```php
db()->table('blog a')->join('cate b', 'a.cid=b.id', 'left')
->field('a.*,b.cname')->select();
//sql:
//SELECT `a`.*,`b`.`cname` FROM `wp_blog` `a` 
//LEFT JOIN `wp_cates` `b` ON `a`.`cid`=`b`.`id`
```

对象前置：

```php
$db = db('blog')->where('status', 1);
$db->where('id', 1)->find(); // status=1 AND id=1
$db->where('id', 2)->find(); // status=1 AND id=2
```

获取SQL：

```php
$sql0 = db('blog')->getSql()->count();
dump($sql0);
$db = db('blog')->getSql();
$sql1 = $db->where('id', 1)->find();
$sql2 = $db->order('id DESC')->select();
dump($sql1, $sql2);
```

其它前置：

```php
union       //查询union
group       //设置group查询
having      //设置having查询
using       //USING(多表删除)
extra       //设置查询额外参数
duplicate   //设置DUPLICATE
lock        //查询lock
distinct    //distinct查询
force       //指定强制索引
comment     //查询注释
```

其它后置：

```php
db()->hasTable('blog'); //是否存在表
$blog = db('blog');
$blog->table('site')->getTable(); //获取表名
$blog->getPk(); //获取表主键字段
$blog->getPrefix(); //获取表前缀
$blog->getFieldList(); //获取表字段列表
```

数据删除：

```php
db('blog')->where('id', 1)->delete(); 
db('blog')->delete(1); 
db('blog')->delete([2,3]);
```

> 必须存在删除条件，返回值为受影响条数

数据新增：

```php
$data = [
    ['cname' => '分类1', 'sort' => '1'],
    ['cname' => '分类2', 'sort' => '2'],
];
$cate = db('cate');
$cate->insert($data[0]); //返回影响条数
$cate->insertGetId($data[0]);//返回新增id
$cate->replace($data[0]); //replace
$cate->data($data[0])->insert(); //先设置数据
$cate->insertAll($data); //批量新增
$cate->field('cname')->insertAll($data); //字段限制
```

数据修改：

```php
$data = ['cname' => '分类1', 'sort' => '1'];
$cate = db('cate');
$cate->where('id', 1)->update($data);
$cate->where('id', 1)->setField('cname', '123'); //设置字段值
$cate->where('id', 1)->setInc('sort', 2); //自增(自减setDec)
$cate->where('id', 1)->inc('sort')->update(); //自增(自减dec)
$cate->where('id', 1)->data('sort', ['inc', 1])->update();
```

> 必须存在更新条件，返回值为受影响条数

查询条件：

```php
where('id', 1)->where('cid=1')  //id=1 AND cid=2
where('id', '>', 0)             //支持 =,<>,>,<,>=,<=
where('id', 'in', '1,2')        //in,not in 支持数组
where('name', 'like', '%ad%')   //like,not like
where('id', 'between', '1,3')   //between,not between
where('id=:id AND status=:status', ['id'=>2, 'status'=>1])  //绑定参数
where('user|email', 'admin') //user=admin OR email=admin
where('id&status', 1) //id=1 AND status=1
```

连接方式：

```php
where('id', 1)->where('status', 1); 
where('id', '=', 1, 'or')->where('id',2)->where('status',1); 
```

数组查询：

```php
$map = [];
$map[] = ['id', '=', 1, 'or']; 
$map[] = ['username', 'like', '%admin%'];
$map['status'] = 1;    
where($map)
where(['id'=>1,'status'=>1])
```

事务处理：


```php
db()->startTrans(); //启动事务
$r1 = db('cate')->add(['cname'=>'willphp']);
$r2 = db('blog')->where('id', 1)->setField('hits', 1);
if ($r1 && $r2) {           
    db()->commit(); //提交事务
    echo '提交成功';
} else {
    db()->rollback(); //事务回滚
    echo '提交失败';
}
```

快速事务：

```php
$price = 10;
$r = db()->trans(function() use ($price) {
    db('user')->where('id', 1)->setDec('price', $price);
    db('user')->where('id', 2)->setInc('price', $price);
});
if ($r) { 
    echo '转账成功';
} else {
    echo '转账失败';
}
```