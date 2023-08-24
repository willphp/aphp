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

namespace willphp\core;
/**
 * 跳转模块
 */
trait Jump
{
    protected function _json(int $code = 200, string $msg = '', array $data = null, array $extend = []): void
    {
        Response::json($code, $msg, $data, $extend);
    }

    private function _jumpTo($msg = '', int $code = 400, string $url = null): void
    {
        if (is_array($msg)) {
            $msg = current($msg);
        }
        if (empty($msg)) {
            $msg = Config::init()->get('response.code_msg.' . $code);
        }
        if (!is_null($url)) {
            $url = Route::init()->buildUrl($url);
        } else {
            $url = ($code < 400) ? '' : 'javascript:history.back(-1);';
        }
        if (IS_API || IS_AJAX) {
            $this->_json($code, $msg, null, ['url' => $url]);
        }
        $res = ['status' => ($code < 400) ? 1 : 0, 'msg' => $msg, 'url' => $url];
        echo view('public:jump', $res);
        exit();
    }

    protected function _jump($info, $status = 1, string $url = null): void
    {
        if (is_array($info)) {
            [$msg200, $msg400] = $info;
        } else {
            $msg200 = $msg400 = $info;
        }
        $code = !$status ? 400 : 200;
        $msg = !$status ? $msg400 : $msg200;
        $this->_jumpTo($msg, $code, $url);
    }

    protected function success($msg = '', string $url = null): void
    {
        $this->_jumpTo($msg, 200, $url);
    }

    protected function error($msg = '', int $code = 400, string $url = null): void
    {
        $this->_jumpTo($msg, $code, $url);
    }

    protected function _url(string $url, int $time = 0): void
    {
        $url = Route::init()->buildUrl($url);
        $time = max(0, $time);
        if ($time == 0) {
            header('Location:' . $url);
        } else {
            header("refresh:$time;url=$url");
        }
        exit();
    }

    protected function _check_login(string $auth = 'cookie.user', string $url = 'login/login'): void
    {
        [$auth, $name] = pre_split($auth, 'cookie');
        $func = in_array($auth, ['cookie', 'session']) ? $auth : 'cookie';
        if (!$func('?'.$name)) {
            $this->error('', 401, $url);
        }
    }

    protected function isAjax(): bool
    {
        return IS_AJAX || IS_API;
    }

    protected function isPost(): bool
    {
        return IS_POST;
    }

    protected function isGet(): bool
    {
        return IS_GET;
    }

    protected function isPut(): bool
    {
        return IS_PUT;
    }

    protected function isDelete(): bool
    {
        return IS_DELETE;
    }
}