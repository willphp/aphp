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
namespace middleware\controller;
use Closure;
/**
 * rbac验证
 */
class Rbac
{
    public function run(Closure $next): void
    {
        if (!session('?user')) {
            header('Location:' . url('login/login'));
        }
        if (!$this->check()) {
            halt('', 403);
        }
        $next();
    }

    protected function check(): bool
    {
        $user = session('user');
        $rbac = config('rbac');
        $super_uid = $rbac['super_uid'] ?? 1;
        if ($user['id'] == $super_uid) {
            return true; //超级管理员
        }
        $no_auth_controller = $rbac['no_auth_controller'] ?? ['index', 'error', 'profile', 'api'];
        $controller = get_controller(); //当前控制器
        if (in_array($controller, $no_auth_controller)) {
            return true;
        }
        $action = get_action(); //当前方法
        if (!empty($rbac['no_auth_prefix']) && preg_match('/^' . $rbac['no_auth_prefix'] . '\w{1,12}$/', $action)) {
            return true;
        }
        $no_auth_action = $rbac['no_auth_action'] ?? [];
        if (in_array($action, $no_auth_action) || in_array($controller . '/' . $action, $no_auth_action)) {
            return true;
        }
        $node_auth = ids_filter($user['node_auth']);
        $user_auth = widget('auth')->get($node_auth); //用户权限列表
        return in_array($controller . '/' . $action, $user_auth);
    }
}