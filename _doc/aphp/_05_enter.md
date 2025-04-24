## 访问入口
 
 可通过URL来访问，也可以在根目录使用命令行来访问。
 
 ### 访问方式
 
 URL：
 
 ```html
 http(s)://域名/入口(绑定应用).php/控制器/方法.html?参数=值&参数=值
 ```
 
 命令行：
 
 ```html
php aphpcli [应用@]控制器:方法 参数:值 参数:值
```

### 入口文件
 
默认入口文件是`public/index.php`，可设置多个不同的入口文件来访问不同的应用。

### 域名绑定应用

可在默认入口文件中设置多个域名来绑定多个应用，如：

```php
<?php
use aphp\core\App;
define('ROOT_PATH', strtr(realpath(__DIR__ . '/../'), '\\', '/'));
require ROOT_PATH . '/aphp/bootstrap.php';
//域名绑定应用示例：
//www.aphp.io 默认 index
//cp.aphp.io 绑定 admin
//api.aphp.io 绑定 api
App::init(['*' => 'index', 'cp' => 'admin', 'api.aphp.io' => 'api'])->boot();
```

---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-04-01 23:31:55