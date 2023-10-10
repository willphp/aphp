<?php
/*----------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: 113344.com
 |----------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2023, 113344.com. All Rights Reserved.
 |---------------------------------------------------------------*/
declare(strict_types=1);

namespace middleware;

use Closure;

/**
 * 全局中件间
 */
class Boot
{
    public function run(Closure $next): void
    {
        trace('欢迎使用 '.__POWERED__.' 全局中间件');
        $next();
    }
}