<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
return [
    //全局json返回字段
    'json' => [
        'ret' => 'code', //状态码字段
        'msg' => 'msg', //返回信息字段
        'data' => 'data', //返回数据字段
        'status' => 'status', //状态字段：1成功(状态码<400)，0失败(状态码>=400)
    ],
    'code_msgs' => [
        0 => '请求成功',
        200 => '请求成功',
        204 => '暂无记录',
        400 => '请求错误',
        401 => '请先登录',
        403 => '不存在或权限不足',
        404 => '{$path} 不存在',
        405 => '{$path} 不可访问',
        406 => '{$field} 验证失败',
        412 => '表单令牌验证失败',
        416 => '{$path}：{$param} 参数不足',
        500 => '服务器错误，请稍候访问',
    ],
];