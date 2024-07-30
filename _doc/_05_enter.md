## 框架流程

APHP框架采用MVC构架，M是模型，V是视图，C是控制器。

基本流程：`入口文件(应用) -> 初始化 -> 获取请求 -> 路由分发 -> 控制器(C) -> 数据交互(M) -> 输出响应(V)`

### 访问方式

URL访问：`http(s)://域名/入口(绑定应用).php/控制器/方法.html?参数=值&参数=值`

CLI访问：`php atop [应用@]控制器:方法 参数:值 参数:值`

### 入口文件

默认入口文件`public/index.php` 代码如下：

```php
use aphp\core\App;
define('ROOT_PATH', strtr(realpath(__DIR__ . '/../'), '\\', '/'));
require ROOT_PATH . '/aphp/bootstrap.php';
App::init()->boot(); //默认index(文件名)应用
//App::init(['admin']) //指定admin应用
//域名绑定应用示例：
//www.aphp.io 默认 index
//cp.aphp.io 绑定 admin
//api.aphp.io 绑定 api
//App::init(['*' => 'index', 'cp' => 'admin', 'api.aphp.io' => 'api'])
```

可设置多个入口文件来访问多个应用，如`admin.php`访问`admin`应用。