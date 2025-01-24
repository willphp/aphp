## 数据操作

数据库操作分三层函数逐一加强：连接层(pdo) <<  查询层(db) <<  模型层(model)

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
$d = pdo()->query('select * from ap_news where id=:id', ['id'=>1]); //同id=?', [1]
dump($d);
```

### 查询层

在连接层基础上使用`db`函数进一步构造生成SQL进行数据库链式操作。

获取连接：

```php
db('news'); //表名 (注意：表名不含前缀)
db()->table('news,cate'); //多表
db('news', 'read'); //使用read配置
db('news', ['db_name'=>'db03']); //连接db03数据库
```

基础方法：

```php
db()->hasTable('news'); //是否存在表
$news = db('news');
$news->table('site')->getTable(); //获取表名
$news->getPk(); //获取表主键字段
$news->getPrefix(); //获取表前缀
$news->getFieldList(); //获取表字段列表
```

### 链式前置

设置别名： 

```php
db('news')->alias('a'); 
db()->table('news a');
db()->table('news a,cate b');
```

设置字段：

```php
db('news')->field('id,title'); //支持数组  
db('news')->field('content', true); //字段排除  
db()->table('news a,cate b')->field('a.*,b.name'); //多表字段
```

设置条件：

```php
db('news')->where(['id'=>1,'status'=>1]); //'id=1 AND status=1'
db('news')->where('id', '=', 1, 'or')->where('id, 2); //id=1 OR id=2 
```

条件示例：

```php
where('id', 1)->where('cid=1')  //id=1 AND cid=2
where('id', '>', 0)             //支持 =,<>,>,<,>=,<=
where('id', 'in', '1,2')        //in,not in 支持数组
where('name', 'like', '%ad%')   //like,not like
where('id', 'between', '1,3')   //between,not between
where('id=:id AND status=:status', ['id'=>2, 'status'=>1])  //绑定参数
where('user|email', 'admin') //user=admin OR email=admin
where('id&status', 1) //id=1 AND status=1
where('tag_ids', 'find_in_set', 6) // 6在tag_ids(1,2,6,7)中   
$map = [];
$map[] = ['id', '=', 1, 'or']; 
$map[] = ['username', 'like', '%admin%'];
$map['status'] = 1;    
where($map); //数组方式
```

设置排序：

```php
db('news')->order('id ASC,cid DESC'); //支持数组['id'=>'asc','cid'=>'desc']
db('news')->order('id','desc');
db('news')->order('[rand]'); // 随机排序
```

查询条数：

```php
db('news')->limit(10);
db('news')->limit(0,10); //同'0,10'
```
返回SQL:

```php
$sql = db('news')->getSql()->find();
```

设置缓存：

```php
db('news')->cache()->find();
db('news')->cache(10)->find(); //有效期10秒
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
$news->where('id', 1)->find();
$news->find(1); //同上
$news->getById(1); //同上
$news->where('id', 1)->value('title'); //返回title的值
```

> 单条查询会自动使用 `limit(1)`

多条查询：

```php
db('news')->order('id DESC')->limit(10)->select(); //二维数组
db('cate')->column('name', 'id'); //[id=>name]一维数组
db('cate')->column('id,name'); //返回二维数组
db('cate')->column('*', 'id'); //返回二维数组
db()->getColumn('cate.id=name@status=1'); //一维数组，表名.主键=字段@条件
```

多条分页：

```php
$list = db('news')->order('id DESC')->paginate(5); //每页5条
foreach ($list as $vo) {
    echo '<p>'.$vo['id'].'</p>';
}
echo $list->links(); //分页html 
echo $list->attr('total'); //总记录数
echo $list->attr('current'); //当前页码
echo $list->attr('offset'); //开始数
echo $list->attr('page_size'); //每页记录数
echo $list->attr('page_count'); //总页数
```

分页查询：

```php
$p = 1; //当前页数，从1开始
if (isset($_GET['p'])) {
    $p = max(1, intval($_GET['p']));
}
db('news')->page($p, 10)->select(); //每页10条
```

聚合查询：

```php
db('news')->where('cid', 1)->count(); //总记录数   
db('news')->sum('hits'); //总和
db('news')->min('hits'); //最小值
db('news')->max('hits'); //最大值
db('news')->avg('hits'); //平均值
```

关联查询：

```php
db()->table('news a')->join('cate b', 'a.cid=b.id', 'left')
->field('a.*,b.name')->select();
//sql:
//SELECT `a`.*,`b`.`name` FROM `wp_news` `a` 
//LEFT JOIN `wp_cate` `b` ON `a`.`cid`=`b`.`id`
```

### 数据删除

```php
db('news')->where('id', 1)->delete(); 
db('news')->delete(1); 
db('news')->delete([2,3]);
```

> 必须存在删除条件，返回值为受影响条数

### 数据新增

```php
$data = [
    ['name' => '分类1', 'sort' => '1'],
    ['name' => '分类2', 'sort' => '2'],
];
$cate = db('cate');
$cate->insert($data[0]); //返回影响条数
$cate->insertGetId($data[0]);//返回新增id
$cate->replace($data[0]); //replace
$cate->data($data[0])->insert(); //先设置数据
$cate->insertAll($data); //批量新增
$cate->field('name')->insertAll($data); //字段限制
```

### 数据更新

```php
$data = ['name' => '分类1', 'sort' => '1'];
$cate = db('cate');
$cate->where('id', 1)->update($data);
$cate->where('id', 1)->setField('name', '123'); //设置字段值
$cate->where('id', 1)->setInc('sort', 2); //自增(自减setDec)
$cate->where('id', 1)->inc('sort')->update(); //自增(自减dec)
$cate->where('id', 1)->data('sort', ['inc', 1])->update();
```

> 必须存在更新条件，返回值为受影响条数

### 事务处理

必须使用支持事务的数据库存储引擎。

```php
db()->startTrans(); //启动事务
$r1 = db('cate')->add(['name'=>'aphp']);
$r2 = db('news')->where('id', 1)->setField('hits', 1);
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

>本文档由 [APHP文档系统](https://doc.aphp.top) 生成，文档更新于：2024-10-26 14:08:39