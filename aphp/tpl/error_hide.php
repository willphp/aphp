<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>出错了!</title>
    <link rel="stylesheet" href="<?php echo __STATIC__?>/css/jump.css"/>
</head>
<body>
<div class="error-page">
    <div class="error-page-container">
        <div class="error-page-main">
            <div class="error-page-head">
                <a href="https://www.aphp.top" title="Aphp框架" target="_blank" rel="noopenner noreferrer">Aphp框架</a>
            </div>
            <h2><strong>:(</strong> <?php echo $msg?></h2>
            <div class="error-page-body">
                <p><span class="blue">出错了</span></p>
                <p>请查看 <span class="blue">日志</span> 或开启 <span class="blue">调试</span> 显示错误信息。</p>
            </div>
            <div class="error-page-foot">
                <a href="<?php echo __URL__; ?>" class="green">返回首页</a>
                <a href="javascript:history.back(-1);" class="blue">返回上页</a>
            </div>
        </div>
        <div class="error-page-actions"></div>
    </div>
</div>
</body>
</html>