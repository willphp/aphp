## 安装配置

### 环境要求

- PHP7.4 ~ PHP8.3 
- MySQL5.6 ~ MySQL8.0

### 下载安装

GitHub地址： https://github.com/willphp/aphp

Gitee地址： https://gitee.com/willphp/aphp

Composer安装：`composer create-project willphp/aphp blog --prefer-dist`

###  环境推荐

- 线上：[宝塔面版bt](https://www.bt.cn)
- 本地：[小皮面版phpstudy](https://www.xp.cn)

### 宝塔面版

1. 添加站点：域名 已解析到IP的域名或IP:端口 数据库Mysql
2. 上传并解压安装包到网站目录
3. 设置-网站目录-运行目录到`/public`
4. 设置-伪静态规则(看`url_rewrite.txt`)
5. 修改`config/database.php`中数据库配置
6. 访问域名
7. 可设置定时任务shell命令：`php aphpcli [应用@]命令类:方法 参数值`

### 小皮面版

1. 创建网站：添加域名，如www.aphp.io 勾选创建数据库
2. 解压安装包到网站目录
3. 修改网站根目录到`/public`
4. 设置伪静态规则(看`url_rewrite.txt`)
5. 重命名`env.example.env`为`.env`并修改其中数据库配置
6. 访问http://www.aphp.io

### 伪静态规则

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

### 小技巧

- composer安装后，如无需composer扩展，可删除`vendor`目录
- 可在`config/app.php`中关闭 调试模式 和 调试栏 
- 在开发中出现任何问题，可尝试清空`runtime`目录
- 可查看`runtime/应用名/log`目录中的日志来确保项目正常运行


>本文档由 [APHP文档系统](https://doc.aphp.top) 生成，文档更新于：2024-10-26 14:09:40