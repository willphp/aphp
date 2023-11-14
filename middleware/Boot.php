<?php
/*----------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: 113344.com
 |----------------------------------------------------------------
 | Author: 大松栩 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2023, 113344.com. All Rights Reserved.
 |---------------------------------------------------------------*/
declare(strict_types=1);
namespace middleware;
use Closure;
class Boot
{
    public function run(Closure $next): void
    {
        header('X-Powered-By:WillPHP'.__VERSION__);
        $next();
    }
}