<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);
namespace middleware;
use aphp\core\Request;
use Closure;
/**
 * 表单令牌验证
 */
class Csrf
{
    protected string $token;

    public function run(Closure $next): void
    {
        if (config_get('view.is_form_csrf', false)) {
            $this->setServerToken();
            $token = $this->getClientToken();
            if ($token != $this->token && IS_POST && ($_SERVER['HTTP_HOST'] == Request::init()->getHost(__HISTORY__))) {
                halt('', 412);
            }
        }
        $next();
    }

    // 设置服务端令牌
    protected function setServerToken(): void
    {
        $token = session('csrf_token');
        if (!$token) {
            $token = md5(get_ip().microtime(true));
            session('csrf_token', $token);
        }
        $this->token = $token;
    }

    // 获取客户端令牌
    protected function getClientToken(): string
    {
        $request = Request::init();
        $token = $request->getHeader('X-CSRF-TOKEN');
        return $token ?: $request->getRequest('csrf_token', '');
    }
}