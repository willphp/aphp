<?php
/*------------------------------------------------------------------
 | 示例中间件 2024-08-15 by 无念
 |------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace middleware\controller;

use Closure;

class Test
{
    public function run(Closure $next, array $params = []): void
    {
        trace('开始测试');
        $next();
        trace('结束测试');
    }
}