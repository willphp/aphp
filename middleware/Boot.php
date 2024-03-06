<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
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