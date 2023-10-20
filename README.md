## WillPHP框架

>WillPHP(一鱼框架)是一个轻量级php8开发框架

### 框架特色

- 易学，会ThinkPHP就会WillPHP，可当新手入门学习框架
- 轻量，200KB+，目录和文件结构简单
- 简单，开发只需少量代码，模板语法可定制
- ORM，与ThinkPHP相似，数据库操作简单
- 安全，可自动过滤，自动验证请求参数

### 环境要求

- PHP7.4.3~PHP8.2.x
- PDO等扩展

### 开发手册

开发手册： [https://willphp.gitee.io](https://willphp.gitee.io)

### 下载安装

Gitee地址： [https://gitee.com/willphp/yiyu](https://gitee.com/willphp/yiyu)

GitHub地址： [https://github.com/willphp/yiyu](https://github.com/willphp/yiyu)

### composer

可以使用 composer 命令安装和扩展：

    composer create-project willphp/yiyu blog --prefer-dist

> 如无需composer扩展，建议删除vendor目录，加速框架运行！ 

### URL重写

Apache环境规则`public/.htaccess`文件：

```
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php? [L,E=PATH_INFO:$1]
</IfModule>
```

Nginx环境规则`public/nginx.htaccess`文件：

```
location / {
	if (!-e $request_filename) {
		rewrite  ^(.*)$  /index.php/$1  last;
	}
}
```

### 交流Q群

>QQ群1：325825297 QQ群2：16008861

### 技术支持

官网：[113344.com](http://www.113344.com) 无念(24203741@qq.com) 
