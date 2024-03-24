<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
trait Jump
{
    protected bool $isApi = false;

    protected function success($msg = '', string $url = ''): void
    {
        $this->_msg($msg, 200, $url);
    }

    protected function error($msg = '', int $code = 400, ?string $url = null): void
    {
        $this->_msg($msg, $code, $url);
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
        $this->_msg($msg, $code, $url);
    }

    protected function _msg($msg = '', int $code = 400, ?string $url = null): void
    {
        if (is_array($msg)) {
            $msg = current($msg);
        }
        if (empty($msg)) {
            $msg = Config::init()->get('response.code_msg.' . $code, 'Error...');
        }
        if (IS_CLI) {
            $this->_json($code, $msg);
        }
        if (!is_null($url) && !IS_CLI) {
            $url = Route::init()->buildUrl($url);
        } else {
            $url = ($code < 400) ? '' : 'javascript:history.back(-1);';
        }
        if ($this->isAjax()) {
            $this->_json($code, $msg, null, ['url' => $url]);
        }

        $vars = ['status' => ($code < 400) ? 1 : 0, 'msg' => $msg, 'url' => $url];
        header('Content-type: text/html; charset=utf-8');
        echo View::init()->fetch('public/jump', $vars);
        exit();
    }

    protected function _json(int $code = 200, string $msg = '', array $data = null, array $extend = []): void
    {
        Response::json($code, $msg, $data, $extend);
    }

    protected function _url(string $url, int $time = 0): void
    {
        $url = Route::init()->buildUrl($url);
        if ($time > 0) {
            header("refresh:$time;url=$url");
        } else {
            header('Location:' . $url);
        }
        exit();
    }

    protected function isAjax(): bool
    {
        return IS_AJAX || IS_API || $this->isApi;
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