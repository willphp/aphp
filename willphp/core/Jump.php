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

trait Jump
{
    protected function success($msg = '', string $url = null): void
    {
        if (is_array($msg)) {
            $msg = current($msg);
        }
        if (empty($msg)) {
            $msg = get_config('response.msg.200');
        }
        $url = is_null($url) ? '' : Route::init()->buildUrl($url);
        if (IS_API || IS_AJAX) {
            $this->_json(200, $msg, null, ['url' => $url]);
        }
        $res = ['status' => 1, 'msg' => $msg, 'url' => $url];
        echo view('public:jump', $res);
        exit();
    }

    protected function error($msg = '', int $code = 400, string $url = null): void
    {
        if (empty($msg)) {
            $msg = get_config('response.msg.' . $code);
        }
        if (is_array($msg)) {
            $msg = current($msg);
        }
        $url = is_null($url) ? 'javascript:history.back(-1);' : Route::init()->buildUrl($url);
        if (IS_API || IS_AJAX) {
            $this->_json($code, $msg, null, ['url' => $url]);
        }
        $res = ['status' => 0, 'msg' => $msg, 'url' => $url];
        echo view('public:jump', $res);
        exit;
    }

    protected function _json(int $code = 200, string $msg = '', array $data = null, array $extend = []): void
    {
        Response::json($code, $msg, $data, $extend);
    }

    protected function _jump($info, bool $status = false, string $url = null): void
    {
        $msg = [];
        if (is_array($info)) {
            $msg = $info;
        } else {
            $msg[0] = $msg[1] = $info;
        }
        if ($status) {
            $this->success($msg[0], $url);
        } else {
            $this->error($msg[1]);
        }
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