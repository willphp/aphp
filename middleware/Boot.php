<?php
/*------------------------------------------------------------------
 | 全局中间件 2024-08-15 by 无念
 |------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace middleware;

use Closure;

class Boot
{
    public function run(Closure $next): void
    {
        header('X-Powered-By:APHP' . __VERSION__);
        $next();
    }
}