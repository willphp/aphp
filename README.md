<p align="center">
    <a href="https://github.com/willphp/yiyu">
        <img src="https://avatars.githubusercontent.com/u/94844305?v=4" width="192" alt="aidigu" />
    </a>
</p>
<p align="center">
    <a href="https://github.com/willphp/yiyu"><img src="https://img.shields.io/badge/PHP-7.4.3~8.2.x-blue?style=for-the-badge&color=%238d4bbb" alt="PHP7.4.3~8.2.x"></a>
    <a href="https://github.com/willphp/yiyu"><img src="https://img.shields.io/badge/STABLE-4.7.1-blue?style=for-the-badge&color=%230aa344" alt="Latest Stable Version"></a>
    <a href="hhttps://github.com/willphp/yiyu"><img src="https://img.shields.io/badge/UNSTABLE-4.7.x--DEV-blue?style=for-the-badge&color=%23ff0097" alt="Latest Unstable Version"></a>
    <a href="https://github.com/willphp/yiyu"><img src="https://img.shields.io/badge/SIZE-124KB-blue?style=for-the-badge&color=%23f0c239" alt="Download Size"></a>
    <a href="https://raw.githubusercontent.com/lty628/aidigu/master/LICENSE"><img src="https://img.shields.io/badge/LICENSE-Apache--2.0-blue?style=for-the-badge&color=%23FF0000" alt="Apache-2.0 License"></a>
</p>

## WillPHP Framework

>WillPHP Framework is a lightweight PHP 8 development framework.
---
### The Features

- **It is easy to LEARN**：*If you know  <a href="https://github.com/top-think/think">ThinkPHP</a>, you'll find WillPHP easy to grasp.Of course, if you're not familiar with ThinkPHP, that's perfectly fine. This is a framework designed for beginners, and it's very easy to get started with, even if you are learning from scratch.*
  
- **More Light**：*Only 200KB+, WillPHP boasts a simple directory and file structure.*
  
- **Simple**：*Development requires minimal code, and template syntax is customizable.*
  
- **ORM (Object-Relational Mapping)**：*It is Similar to ThinkPHP, WillPHP offers straightforward database operations.*
  
- **Security**：*Automatically filters and validates incoming request parameters for enhanced security.*
---
### System Requirements

- PHP7.4.3~PHP8.2.x
- Required extensions such as PDO,etc.
---
### Development Manual

Development Manual： [https://willphp.gitee.io](https://willphp.gitee.io)

### Installation

Gitee Repository： [https://gitee.com/willphp/yiyu](https://gitee.com/willphp/yiyu)

GitHub Repository： [https://github.com/willphp/yiyu](https://github.com/willphp/yiyu)

### Composer

You can use the '*composer*' command to install and extend:

    composer create-project willphp/yiyu blog --prefer-dist

> If no composer extension is needed, it is recommended to delete the vendor directory to speed up the framework's operation!
---
### URL Rewriting Rules

Apache Rewriting Rule `public/.htaccess` File：

```
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php? [L,E=PATH_INFO:$1]
</IfModule>
```

Nginx Rewriting Rule `public/nginx.htaccess` File：

```
location / {
	if (!-e $request_filename) {
		rewrite  ^(.*)$  /index.php/$1  last;
	}
}
```
---
### Talk to us!

>QQ Group 1：325825297 Q Group 2：16008861

### Contact & Support

Official Website:：[113344.com](http://www.113344.com) Our Email：大松栩(24203741@qq.com) 

>Also you can communicate with us through issues, raise your questions, or provide suggestions. We will do our best to answer and make improvements.  Thank you for your support and encouragement for this project. We will continue to work hard and strive for further progress！
---
### Copyright ©
**This project adheres to the** ***Apache-2.0 License***
