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

class Auth
{
    public function run(Closure $next): void
    {
        if (!session('user')) {
            header('Location:' . url('login/login'));
        }
        if (!$this->checkAuth()) {
            halt('', 403);
        }
        $next();
    }

    protected function checkAuth(): bool
    {
        $rbac = config('rbac');
        $auth_type = $rbac['auth_type'] ?? 1;
        $super_uid = $rbac['super_uid'] ?? 1;
        $no_auth = $rbac['no_auth'] ?? ['index', 'api'];
        $user = session('user');
        if ($user['id'] == $super_uid) {
            return true; //超级管理员
        }
        $controller = get_controller(); //当前控制器
        if (in_array($controller, $no_auth)) {
            return true; //无须验证的控制器
        }
        $auth = widget('auth')->get($user['auth']); //当前用户权限
        if ($auth_type == 1) {
            //只验证控制器
            $auth = array_map(fn($v)=>strstr($v, '/', true), $auth);
            return in_array($controller, array_unique($auth));
        }
        $action = get_action(); //当前方法
        return in_array($controller.'/'.$action, $auth);
    }
}