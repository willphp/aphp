## 一鱼框架APHP

APHP框架(原WillPHP框架)是一个MVC超轻量级PHP8开发框架

### 框架特点

- 极简200KB+，新手快速入门
- ORM数据交互，简单方便
- 模型可自动验证，处理，过滤数据
- 超简单模板标签，自由定制

### 环境要求

- PHP 7.4~PHP 8.4
- MySQL5.6~8.0

### 开发指南

内置文档：查看`_doc`目录

在线文档： [https://doc.aphp.top](https://doc.aphp.top)

### 更新日志

查看内置文档：`_doc/aphp/_02_update.md`

### 下载安装

GitHub地址： https://github.com/willphp/aphp

Gitee地址： https://gitee.com/willphp/aphp

Composer安装：`composer create-project willphp/aphp blog --prefer-dist`

> 如无需composer扩展，建议删除vendor目录，加速框架运行！ 

### 安装指南

1. 上传并解压安装包到网站目录
2. 设置-网站目录-运行目录到`/public`
3. 设置-伪静态规则(看`url_rewrite.txt`)
4. [本地]重命名`env.example.env`为`.env`并修改其中数据库配置
5. [线上]修改`config/database.php`中数据库配置

### 伪静态设置

Nginx规则：

```
location / {
	if (!-e $request_filename) {
		rewrite  ^(.*)$  /index.php/$1  last;
	}
}
```

Apache规则：

```
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php? [L,E=PATH_INFO:$1]
</IfModule>
```

### 技术支持

QQ群1：325825297  QQ群2：16008861

官网：[aphp.top](https://www.aphp.top) 作者：无念(24203741@qq.com) 