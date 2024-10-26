## 框架流程

APHP框架采用MVC构架，M是模型，V是视图，C是控制器。

基本流程：`入口文件(应用) -&gt; 初始化 -&gt; 获取请求 -&gt; 路由分发 -&gt; 控制器(C) -&gt; 数据交互(M) -&gt; 输出响应(V)`

### 访问方式

URL访问：`http(s)://域名/入口(绑定应用).php/控制器/方法.html?参数=值&amp;参数=值`

CLI访问：`php aphpcli [应用@]控制器:方法 参数:值 参数:值`

### 入口文件

默认入口文件`public/index.php` 代码如下：

```php
use aphp\core\App;
define('ROOT_PATH', strtr(realpath(__DIR__ . '/../'), '\\', '/'));
require ROOT_PATH . '/aphp/bootstrap.php';
App::init()-&gt;boot(); //默认index(文件名)应用
//App::init(['admin']) //指定admin应用
//域名绑定应用示例：
//www.aphp.io 默认 index
//cp.aphp.io 绑定 admin
//api.aphp.io 绑定 api
//App::init(['*' =&gt; 'index', 'cp' =&gt; 'admin', 'api.aphp.io' =&gt; 'api'])
```

可设置多个入口文件来访问多个应用，如`admin.php`访问`admin`应用。

>本文档由 [APHP文档系统](https://doc.aphp.top) 生成，文档更新于：2024-10-25 15:47:26