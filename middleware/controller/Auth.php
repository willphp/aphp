<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);
namespace middleware\controller;
use Closure;
/**
 * 通用登录验证
 */
class Auth
{
    public function run(Closure $next): void
    {
        if (!session('?user')) {
            if (IS_AJAX) {
                halt('', 401); // AJAX未登录提示
            }
            header('Location:' . url('login/login')); // 转跳到登录页
            exit();
        }
        $next();
    }
}