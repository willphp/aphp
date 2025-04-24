## 目录结构

框架基本目录结构如下：

```php
    www  WEB部署目录
    │
    ├─_doc                  开发文档
    │
    ├─aphp                  框架核心
    │
    ├─app                   应用目录
    │  ├─index              默认应用
    │  │  ├─command         命令
    │  │  ├─config          配置
    │  │  ├─controller      控制器
    │  │  ├─model           模型
    │  │  ├─view            视图
    │  │  └─widget          部件
    │  |
    │  └─common.php         公共函数
    │
    ├─config                配置目录
    │  ├─app.php            应用
    │  ├─cache.php          缓存
    │  ├─cookie.php         Cookie
    │  ├─database.php       数据库
    │  ├─debug_bar.php      调试栏
    │  ├─email.php          邮件
    │  ├─filter.php         自动过滤
    │  ├─middleware.php     中间件
    │  ├─pagination.php     分页
    │  ├─response.php       响应
    │  ├─route.php          路由
    │  ├─session.php        Session
    │  ├─site.php           网站  
    │  ├─template.php       模板引擎
    │  ├─upload.php         上传
    │  ├─validate.php       验证规则
    │  └─view.php           视图
    │
    ├─public                WEB目录(对外访问目录)
    │  ├─static             静态资源(css,js,img)
    │  ├─uploads            图片上传目录(可写)
    │  ├─index.php          入口文件
    │  ├─.htaccess          apache重写
    │  └─nginx.htaccess     nginx重写
    |
    ├─extend                扩展类库        
    ├─middleware            中间件
    ├─route                 路由跳转
    ├─runtime               运行时目录(可写)
    ├─template              前台模板(可选)
    ├─vendor                composer类库(可选)
    |
    ├─.gitignore            git忽略配置
    ├─aphpcli               命令行入口
    ├─env.example.env       本地.env示例
    ├─composer.json         composer设置
    ├─LICENSE               授权协议
    ├─README.md             README文件   
    └─url_rewrite.txt       伪静态规则说明
```

> 可安装 `composer` 扩展，无需可删除 `vendor` 目录

---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-04-01 22:41:49