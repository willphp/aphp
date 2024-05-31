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
            if (IS_AJAX) {
                halt('', 401);
            }
            header('Location:' . url('login/login'));
            exit();
        }
        $next();
    }
}