<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>欢迎使用{:site('site_title')}</title>
    <link rel="shortcut icon" href="__ROOT__/favicon.ico" type="image/x-icon"/>
    <style>
        body,code,dd,div,dl,dt,fieldset,form,h1,h2,h3,h4,h5,h6,input,legend,li,ol,p,pre,td,textarea,th,ul{margin:0;padding:0}
        body{background:#f0f1f3;font-size:16px;font-family:Tahoma,Arial,sans-serif;color:#111}
        h1,h2,h3,h4,h5,h6,strong{font-weight:700}
        a{color:#428bca}
        .cl{zoom:1}
        .cl:after{content:".";display:block;height:0;clear:both;visibility:hidden}
        .blue{color:#4288ce}
        .aphp-page{max-width:680px;padding:10px;margin:60px auto 0;background:#f0f1f3;overflow:hidden;word-break:keep-all;word-wrap:break-word}
        .aphp-page-container{position:relative;z-index:1}
        .aphp-page-main{position:relative;background:#f9f9f9;margin:0 auto;-ms-box-sizing:border-box;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding:25px 30px 30px 30px}
        .aphp-page-main:before{content:'';display:block;background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAmkAAAAHCAIAAADcck2GAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAACFSURBVHja7NWhDgFxHMDxOxNsNGc22aaZKXZZ5KKX8Fye498VgUJDEq6QCDbdI/zu83mDb/rm1XaXNcBkvXl+8vCZnUu63uvwmYtqdXt3w2eOXufD/hg+c1bOH71x+MzBtz6lFD6zGPbb02UTntLKAADvBADvBADvBADvBADvBAD+/QQYAPeEFhyocrThAAAAAElFTkSuQmCC);height:7px;position:absolute;top:-7px;width:100%;left:0}
        .aphp-page-main h2{font-size:22px;color:#333;font-weight:400}
        .aphp-page-main h2 strong{font-size:50px;font-weight:400;margin-right:1px}
        .aphp-page-head{text-align:right;color:#4288ce}
        .aphp-page-head a{font-size:14px;color:#4288ce}
        .aphp-page-menu{padding:10px;border-bottom:1px dashed #999}
        .aphp-page-body{}
        .aphp-page-body p{font-size:14px;padding:5px 0;color:#666;line-height:25px}
        .aphp-page-body ol{padding-left:15px}
        .aphp-page-body ul{padding-left:15px}
        .aphp-page-body li{font-size:14px;padding:5px 0;color:#666;line-height:20px}
        .aphp-page-body h4{font-size:15px;padding:10px 0;font-weight:400;}
        .aphp-page-foot{padding:0 0 10px 0;border-bottom:1px dashed #999}
        .aphp-page-foot a{float:right;height:30px;line-height:30px;padding:0 15px;margin:0;font-size:14px;border:none;margin-left:5px;text-decoration:none}
        .aphp-page-foot a.green{background:#4cae4c;color:#fff}
        .aphp-page-foot a.blue{background:#4288ce;color:#fff}
        .aphp-page-foot a.red{background:#FF69B4;color:#fff}
        .aphp-copyright{padding-top:5px;font-size:14px;text-align:right;color:#999}
        .aphp-page-actions{font-size:0;z-index:100}
        .aphp-page-actions:before{content:'';display:block;position:absolute;z-index:-1;bottom:17px;left:50px;width:200px;height:10px;-moz-box-shadow:4px 5px 31px 11px #999;-webkit-box-shadow:4px 5px 31px 11px #999;box-shadow:4px 5px 31px 11px #999;-moz-transform:rotate(-4deg);-webkit-transform:rotate(-4deg);-ms-transform:rotate(-4deg);-o-transform:rotate(-4deg);transform:rotate(-4deg)}
        .aphp-page-actions:after{content:'';display:block;position:absolute;z-index:-1;bottom:17px;right:50px;width:200px;height:10px;-moz-box-shadow:4px 5px 31px 11px #999;-webkit-box-shadow:4px 5px 31px 11px #999;box-shadow:4px 5px 31px 11px #999;-moz-transform:rotate(4deg);-webkit-transform:rotate(4deg);-ms-transform:rotate(4deg);-o-transform:rotate(4deg);transform:rotate(4deg)}
    </style>
</head>
<body>
<div class="aphp-page">
    <div class="aphp-page-container">
        <div class="aphp-page-main">
            <div class="aphp-page-head">
                <a href="https://www.aphp.top" title="aphp官网" target="_blank">APHP框架</a>
            </div>
            <h2><strong>Σ( ° △ °|||)︴</strong>欢迎使用__POWERED__</h2>
            <div class="aphp-page-foot cl">
                <a href="https://qm.qq.com/cgi-bin/qm/qr?k=U7SzseDDXSbG9sB1CTEf5U10oFJOKR8-&jump_from=webapi" target="_blank" class="red">Q群:325825297</a>
                <a href="https://gitee.com/willphp/aphp" target="_blank" class="blue">下载新版</a>
                <a href="https://doc.aphp.top/" target="_blank" class="green">开发手册</a>
            </div>
            <div class="aphp-page-menu">
                <a href="{:url('index/index')}">首页</a> | <a href="{:url('index/ok')}">成功</a> | <a href="{:url('abc/abc')}">404</a>
            </div>
            <div class="aphp-page-body cl">
                <p style="color:red;">公告：{:site('site_notice')}</p>
                <h4>开始使用</h4>
                <ol>
                    <li>[本地]重命名<code class="blue">env.example.env</code>为<code class="blue">.env</code>命今行：<code class="blue">rename env.example.env .env</code></li>
                    <li>[本地]打开<code class="blue">.env</code>文件配置本地数据库</li>
                    <li>[上线]打开<code class="blue">config/database.php</code>配置服务器数据库</li>
                    <li>[上线]删除<code class="blue">.env</code>文件或不上传<code class="blue">.env</code>文件</li>
                </ol>
                <p>时间: {:date('Y-m-d H:i:s')}</p>
            </div>
            <div class="aphp-copyright cl">
                CopyRight &copy; 2020-{:date('Y')} <a href="https://www.aphp.top" title="aphp官网" target="_blank">aphp.top</a>
                by DaSongXu
            </div>
        </div>
        <div class="aphp-page-actions"></div>
    </div>
</div>
</body>
</html>