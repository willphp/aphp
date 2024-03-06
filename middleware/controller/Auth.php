<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace middleware\controller;

use Closure;

class Auth
{
    public function run(Closure $next): void
    {
        if (!session('?user')) {
            header('Location:' . url('login/login'));
        }
        $next();
    }
}