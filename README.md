## 一鱼PHP框架

 告别繁琐，精简开发！一鱼PHP框架(APHP)是一个MVC超轻量级PHP8框架。

### 框架特色

- 代码超轻量(200KB+)，按需加载
- 数据库链式操作
- 模型三大自动：验证，处理，过滤
- 模板标签简单，可定制
- 支持Redis缓存，命令行可生成MVC

### 环境要求

- PHP环境：PHP8.1 ~ PHP8.5
- 数据库：MySQL5.6 ~ MySQL8.0

### 相关文档

开发文档与更新日志： [https://doc.aphp.top](https://doc.aphp.top)

### 仓库地址

Gitee地址： https://gitee.com/willphp/aphp

GitHub地址： https://github.com/willphp/aphp

### 环境推荐

线上：[宝塔面版BT](https://www.bt.cn)  本地：[小皮面版phpstudy](https://www.xp.cn) 

### 宝塔bt

1. 添加站点：域名 已解析到IP的域名或IP:端口 数据库Mysql
2. 上传并解压安装包到网站目录
3. 设置—>网站目录-运行目录到`/public`
4. 设置—>`伪静态`规则
5. 修改`config/database.php`中的数据库配置
6. 或重命名`env.example.env`为`.env`并修改其中的数据库配置
7. 访问域名
8. 可设置定时任务shell命令：`php aphpcli [应用@]命令类:方法 参数值`

### phpstudy

1. 创建网站：添加域名，如`www.aphp.to` 勾选创建数据库
2. 解压安装包到网站目录
3. 修改网站根目录到`/public`
4. 设置`伪静态`规则
5. 重命名`env.example.env`为`.env`并修改其中数据库配置
6. 访问`http://www.aphp.to`

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

### 开发案例

- 一鱼CMS(aphpcms)：https://www.aphpcms.com 
- 一鱼文档(aphpdoc)：https://doc.aphp.top  
- 一鱼后台(aphpadmin)：https://gitee.com/willphp/aphpadmin

### 技术支持

框架官网：https://www.aphp.top QQ群1：325825297 QQ群2：16008861 作者：无念(24203741@qq.com) 