## 数据操作

数据库操作分三层函数逐一加强：连接层(pdo) &amp;lt;&amp;lt;  查询层(db) &amp;lt;&amp;lt;  模型层(model)

### 连接层

使用`pdo`函数可获取数据库连接对象进行操作，方法如下：

```php
//getConfig		获取配置
//query			执行sql查询
//execute		执行sql操作
//getNumRows	获取响应条数
//getInsertId	获取最后插入ID
//getRealSql	获取真实SQL
//trans			事务闭包操作
//startTrans	开启事务
//commit		提交事务
//rollback		回滚事务
$d = pdo()-&gt;query('select * from ap_news where id=:id', ['id'=&gt;1]); //同id=?', [1]
dump($d);
```

### 查询层

在连接层基础上使用`db`函数进一步构造生成SQL进行数据库链式操作。

获取连接：

```php
db('news'); //表名 (注意：表名不含前缀)
db()-&gt;table('news,cate'); //多表
db('news', 'read'); //使用read配置
db('news', ['db_name'=&gt;'db03']); //连接db03数据库
```

基础方法：

```php
db()-&gt;hasTable('news'); //是否存在表
$news = db('news');
$news-&gt;table('site')-&gt;getTable(); //获取表名
$news-&gt;getPk(); //获取表主键字段
$news-&gt;getPrefix(); //获取表前缀
$news-&gt;getFieldList(); //获取表字段列表
```

### 链式前置

设置别名： 

```php
db('news')-&gt;alias('a'); 
db()-&gt;table('news a');
db()-&gt;table('news a,cate b');
```

设置字段：

```php
db('news')-&gt;field('id,title'); //支持数组  
db('news')-&gt;field('content', true); //字段排除  
db()-&gt;table('news a,cate b')-&gt;field('a.*,b.name'); //多表字段
```

设置条件：

```php
db('news')-&gt;where(['id'=&gt;1,'status'=&gt;1]); //'id=1 AND status=1'
db('news')-&gt;where('id', '=', 1, 'or')-&gt;where('id, 2); //id=1 OR id=2 
```

条件示例：

```php
where('id', 1)-&gt;where('cid=1')  //id=1 AND cid=2
where('id', '&gt;', 0)             //支持 =,&lt;&gt;,&gt;,&lt;,&gt;=,&lt;=
where('id', 'in', '1,2')        //in,not in 支持数组
where('name', 'like', '%ad%')   //like,not like
where('id', 'between', '1,3')   //between,not between
where('id=:id AND status=:status', ['id'=&gt;2, 'status'=&gt;1])  //绑定参数
where('user|email', 'admin') //user=admin OR email=admin
where('id&amp;status', 1) //id=1 AND status=1
where('tag_ids', 'find_in_set', 6) // 6在tag_ids(1,2,6,7)中   
$map = [];
$map[] = ['id', '=', 1, 'or']; 
$map[] = ['username', 'like', '%admin%'];
$map['status'] = 1;    
where($map); //数组方式
```

设置排序：

```php
db('news')-&gt;order('id ASC,cid DESC'); //支持数组['id'=&gt;'asc','cid'=&gt;'desc']
db('news')-&gt;order('id','desc');
```

查询条数：

```php
db('news')-&gt;limit(10);
db('news')-&gt;limit(0,10); //同'0,10'
```
返回SQL:

```php
$sql = db('news')-&gt;getSql()-&gt;find();
```

设置缓存：

```php
db('news')-&gt;cache()-&gt;find();
db('news')-&gt;cache(10)-&gt;find(); //有效期10秒
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

### 数据查询

单条查询：

```php
$news = db('news');
$news-&gt;where('id', 1)-&gt;find();
$news-&gt;find(1); //同上
$news-&gt;getById(1); //同上
$news-&gt;where('id', 1)-&gt;value('title'); //返回title的值
```

&gt; 单条查询会自动使用 `limit(1)`

多条查询：

```php
db('news')-&gt;order('id DESC')-&gt;limit(10)-&gt;select(); //二维数组
db('cate')-&gt;column('name', 'id'); //[id=&gt;name]一维数组
db('cate')-&gt;column('id,name'); //返回二维数组
db('cate')-&gt;column('*', 'id'); //返回二维数组
db()-&gt;getColumn('cate.id=name@status=1'); //一维数组，表名.主键=字段@条件
```

多条分页：

```php
$list = db('news')-&gt;order('id DESC')-&gt;paginate(5); //每页5条
foreach ($list as $vo) {
    echo '&lt;p&gt;'.$vo['id'].'&lt;/p&gt;';
}
echo $list-&gt;links(); //分页html 
echo $list-&gt;attr('total'); //总记录数
echo $list-&gt;attr('current'); //当前页码
echo $list-&gt;attr('offset'); //开始数
echo $list-&gt;attr('page_size'); //每页记录数
echo $list-&gt;attr('page_count'); //总页数
```

分页查询：

```php
$p = 1; //当前页数，从1开始
if (isset($_GET['p'])) {
    $p = max(1, intval($_GET['p']));
}
db('news')-&gt;page($p, 10)-&gt;select(); //每页10条
```

聚合查询：

```php
db('news')-&gt;where('cid', 1)-&gt;count(); //总记录数   
db('news')-&gt;sum('hits'); //总和
db('news')-&gt;min('hits'); //最小值
db('news')-&gt;max('hits'); //最大值
db('news')-&gt;avg('hits'); //平均值
```

关联查询：

```php
db()-&gt;table('news a')-&gt;join('cate b', 'a.cid=b.id', 'left')
-&gt;field('a.*,b.name')-&gt;select();
//sql:
//SELECT `a`.*,`b`.`name` FROM `wp_news` `a` 
//LEFT JOIN `wp_cate` `b` ON `a`.`cid`=`b`.`id`
```

### 数据删除

```php
db('news')-&gt;where('id', 1)-&gt;delete(); 
db('news')-&gt;delete(1); 
db('news')-&gt;delete([2,3]);
```

&gt; 必须存在删除条件，返回值为受影响条数

### 数据新增

```php
$data = [
    ['name' =&gt; '分类1', 'sort' =&gt; '1'],
    ['name' =&gt; '分类2', 'sort' =&gt; '2'],
];
$cate = db('cate');
$cate-&gt;insert($data[0]); //返回影响条数
$cate-&gt;insertGetId($data[0]);//返回新增id
$cate-&gt;replace($data[0]); //replace
$cate-&gt;data($data[0])-&gt;insert(); //先设置数据
$cate-&gt;insertAll($data); //批量新增
$cate-&gt;field('name')-&gt;insertAll($data); //字段限制
```

### 数据更新

```php
$data = ['name' =&gt; '分类1', 'sort' =&gt; '1'];
$cate = db('cate');
$cate-&gt;where('id', 1)-&gt;update($data);
$cate-&gt;where('id', 1)-&gt;setField('name', '123'); //设置字段值
$cate-&gt;where('id', 1)-&gt;setInc('sort', 2); //自增(自减setDec)
$cate-&gt;where('id', 1)-&gt;inc('sort')-&gt;update(); //自增(自减dec)
$cate-&gt;where('id', 1)-&gt;data('sort', ['inc', 1])-&gt;update();
```

&gt; 必须存在更新条件，返回值为受影响条数

### 事务处理

必须使用支持事务的数据库存储引擎。

```php
db()-&gt;startTrans(); //启动事务
$r1 = db('cate')-&gt;add(['name'=&gt;'aphp']);
$r2 = db('news')-&gt;where('id', 1)-&gt;setField('hits', 1);
if ($r1 &amp;&amp; $r2) {           
    db()-&gt;commit(); //提交事务
    echo '提交成功';
} else {
    db()-&gt;rollback(); //事务回滚
    echo '提交失败';
}
```

快速事务：

```php
$price = 10;
$r = db()-&gt;trans(function() use ($price) {
    db('user')-&gt;where('id', 1)-&gt;setDec('price', $price);
    db('user')-&gt;where('id', 2)-&gt;setInc('price', $price);
});
if ($r) { 
    echo '转账成功';
} else {
    echo '转账失败';
}
```

>本文档由 [APHP文档系统](https://doc.aphp.top) 生成，文档更新于：2024-10-25 15:49:35