## 安装指南

下载压缩包解压到网站环境目录，配置运行目录至 `/public` ，再配置 `伪静态` 规则。

### 安装要求

- PHP7.4 ~ PHP8.4
- MySQL5.6 ~MySQL8.0

### 环境推荐

线上：[宝塔面版BT](https://www.bt.cn)  本地：[小皮面版phpstudy](https://www.xp.cn) 

### 宝塔BT

1. 添加站点：域名 已解析到IP的域名或IP:端口 数据库Mysql
2. 上传并解压安装包到网站目录
3. 设置—>网站目录-运行目录到`/public`
4. 设置—>伪静态规则(看`url_rewrite.txt`)
5. 修改`config/database.php`中数据库配置
6. 访问域名
7. 可设置定时任务shell命令：`php aphpcli [应用@]命令类:方法 参数值`

### phpstudy

1. 创建网站：添加域名，如`www.aphp.io` 勾选创建数据库
2. 解压安装包到网站目录
3. 修改网站根目录到`/public`
4. 设置伪静态规则(看`url_rewrite.txt`)
5. 重命名`env.example.env`为`.env`并修改其中数据库配置
6. 访问`http://www.aphp.io`

### 伪静态规则

Nginx规则：

```php
location / {
	if (!-e $request_filename) {
		rewrite  ^(.*)$  /index.php/$1  last;
	}
}
```

Apache规则：

```php
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php? [L,E=PATH_INFO:$1]
</IfModule>
```



---

本文档由 [AphpDoc](https://doc.aphp.top) 生成，更新于：2025-04-10 11:08:52