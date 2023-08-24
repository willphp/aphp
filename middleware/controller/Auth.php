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

namespace middleware\controller;

use Closure;

/**
 * 登录验证中间件
 */
class Auth
{
    public function run(Closure $next): void
    {
        if (!session('user')) {
            header('Location:' . url('login/login'));
        }
        $next();
    }
}