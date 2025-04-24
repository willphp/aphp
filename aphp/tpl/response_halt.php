 <!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>出错了!</title>
    <style>
        body,code,dd,div,dl,dt,fieldset,form,h1,h2,h3,h4,h5,h6,input,legend,li,ol,p,pre,td,textarea,th,ul{margin:0;padding:0}
        body{background:#f0f1f3;font-size:16px;font-family:Tahoma,Arial,sans-serif;color:#111}
        h1,h2,h3,h4,h5,h6,strong{font-weight:700}
        a{color:#4288ce;text-decoration:none}
        a:hover{text-decoration:none}
        .blue{color:#4288ce}
        .error-page{max-width:580px;padding:10px;margin:60px auto 0;background:#f0f1f3;overflow:hidden;word-break:keep-all;word-wrap:break-word}
        .error-page-container{position:relative;z-index:1}
        .error-page-main{position:relative;background:#f9f9f9;margin:0 auto;-ms-box-sizing:border-box;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding:25px 30px 30px 30px}
        .error-page-main:before{content:'';display:block;background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAmkAAAAHCAIAAADcck2GAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAACFSURBVHja7NWhDgFxHMDxOxNsNGc22aaZKXZZ5KKX8Fye498VgUJDEq6QCDbdI/zu83mDb/rm1XaXNcBkvXl+8vCZnUu63uvwmYtqdXt3w2eOXufD/hg+c1bOH71x+MzBtz6lFD6zGPbb02UTntLKAADvBADvBADvBADvBADvBAD+/QQYAPeEFhyocrThAAAAAElFTkSuQmCC);height:7px;position:absolute;top:-7px;width:100%;left:0}
        .error-page-main h2{font-size:24px;color:#a94442;font-weight:400;padding-bottom:20px;border-bottom:1px dashed #999}
        .error-page-main h2 strong{font-size:54px;font-weight:400;margin-right:20px}
        .error-page-head{text-align:right}
        .error-page-head a{font-size:14px;color:#999}
        .error-page-body{padding-top:10px}
        .error-page-body p{font-size:14px;padding:10px 0;color:#666;line-height:25px}
        .error-page-body h4{font-size:18px;padding:5px 0 20px 0;font-weight:400;color:#a94442}
        .error-page-foot{padding:15px 0 25px 0;border-top:1px dashed #999}
        .error-page-foot a{float:right;height:30px;line-height:30px;padding:0 15px;font-size:14px;border:none;margin:0 0 0 5px}
        .error-page-foot a.green{background:#4cae4c;color:#fff}
        .error-page-foot a.blue{background:#4288ce;color:#fff}
        .error-page-actions{font-size:0;z-index:100}
        .error-page-actions:before{content:'';display:block;position:absolute;z-index:-1;bottom:17px;left:50px;width:200px;height:10px;-moz-box-shadow:4px 5px 31px 11px #999;-webkit-box-shadow:4px 5px 31px 11px #999;box-shadow:4px 5px 31px 11px #999;-moz-transform:rotate(-4deg);-webkit-transform:rotate(-4deg);-ms-transform:rotate(-4deg);-o-transform:rotate(-4deg);transform:rotate(-4deg)}
        .error-page-actions:after{content:'';display:block;position:absolute;z-index:-1;bottom:17px;right:50px;width:200px;height:10px;-moz-box-shadow:4px 5px 31px 11px #999;-webkit-box-shadow:4px 5px 31px 11px #999;box-shadow:4px 5px 31px 11px #999;-moz-transform:rotate(4deg);-webkit-transform:rotate(4deg);-ms-transform:rotate(4deg);-o-transform:rotate(4deg);transform:rotate(4deg)}
    </style>
</head>
<body>
<div class="error-page">
    <div class="error-page-container">
        <div class="error-page-main">
            <div class="error-page-head">
                <a href="https://www.aphp.top" title="APHP官网" target="_blank" rel="noopenner noreferrer"><?php echo __POWERED__?></a>
            </div>
            <h2><strong>:(</strong> <?php echo $msg?></h2>
            <div class="error-page-body">
                <p>页面自动 <a id="href" href="javascript:history.back(-1);">跳转</a> 等待时间 <strong id="wait">5</strong> 秒</p>
            </div>
            <div class="error-page-foot">
                <a href="<?php echo __URL__; ?>" class="green">返回首页</a>
                <a href="javascript:history.back(-1);" class="blue">返回上页</a>
            </div>
        </div>
        <div class="error-page-actions"></div>
    </div>
</div>
<script type="text/javascript">
    (function () {
        const wait = document.getElementById('wait');
        const href = document.getElementById('href').href;
        window.setInterval(function() {
            const time = --wait.innerHTML;
            if (time <= 0) {
                location.href = href;
            }
        }, 1000);
    })();
</script>
</body>
</html>