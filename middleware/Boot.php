<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace middleware;

use Closure;

/**
 * 全局
 */
class Boot
{
    public function run(Closure $next): void
    {
        header('X-Powered-By:APHP' . __VERSION__);
        $next();
    }
}