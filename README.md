## WillPHP框架

>WillPHPv4是一个轻量级php开发框架

### 环境要求

- PHP版本需要 >=7.4.3
- PDO扩展

### 开发手册

开发手册： [https://willphp.gitee.io](https://willphp.gitee.io)

### 下载安装

Gitee地址： [https://gitee.com/willphp/yiyu](https://gitee.com/willphp/yiyu)

GitHub地址： [https://github.com/willphp/yiyu](https://github.com/willphp/yiyu)

### composer安装

可以使用 composer 命令进行安装：

    composer create-project willphp/yiyu blog --prefer-dist

>如无需composer加载，可删除vendor目录，使用框架自动加载！

### URL重写

[Apache] .htaccess

```
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php? [L,E=PATH_INFO:$1]
</IfModule>
```

[Nginx] nginx.htaccess

```
location / {
	if (!-e $request_filename) {
		rewrite  ^(.*)$  /index.php/$1  last;
	}
}
```

### 交流Q群

>QQ群1：325825297 QQ群2：16008861

### 关于框架

官网：[113344.com](http://www.113344.com) 无念(24203741@qq.com) 
