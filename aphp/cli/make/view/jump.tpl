<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>跳转提示</title>
    <link rel="stylesheet" href="__STATIC__/css/jump.css"/>
</head>
<body>
<div class="error-page">
    <div class="error-page-container">
        <div class="error-page-main">
            <div class="error-page-head">
                <a href="https://www.aphp.top" title="Aphp框架" target="_blank" rel="noopenner noreferrer">Aphp框架</a>
            </div>
            {if $status==1:}
                <h2 style="color:#4288ce;"><strong>:)</strong> {$msg}</h2>
            {else:}
                <h2 style="color:#a94442;"><strong>:(</strong> {$msg}</h2>
            {/if}
            <div class="error-page-body">
                <p>页面自动 <a id="href" href="{$url}">跳转</a> 等待时间 <b id="wait">5</b> 秒</p>
            </div>
            <div class="error-page-foot">
                <a href="__URL__" class="green">返回首页</a>
                <a href="{$url}" class="blue">确认</a>
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