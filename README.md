## 一鱼框架APHP

APHP框架(原WillPHP框架)是一个MVC超轻量级PHP8开发框架

### 框架特点

- 大小200KB+，新手快速入门
- ORM，数据交互简单
- 模型可自动验证，处理，过滤数据

### 环境要求

- PHP 7.4~PHP 8.3
- MySQL5.6~8.0

### 开发手册

开发手册： [https://doc.aphp.top](https://doc.aphp.top)

### 下载安装

GitHub地址： https://github.com/willphp/aphp

Gitee地址： https://gitee.com/willphp/aphp

Composer安装：`composer create-project willphp/aphp blog --prefer-dist`

> 如无需composer扩展，建议删除vendor目录，加速框架运行！ 

### URL重写

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

官网：[aphp.top](https://www.aphp.top) 作者：大松栩(24203741@qq.com) 