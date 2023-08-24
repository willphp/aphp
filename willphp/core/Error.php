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

use Throwable;

/**
 * 错误处理类
 */
class Error
{
    use Single;

    protected array $msg = []; //错误信息

    private function __construct()
    {
        error_reporting(0);
        set_error_handler([$this, 'error'], E_ALL | E_STRICT);
        set_exception_handler([$this, 'exception']);
    }

    //获取错误信息
    public function getError(): array
    {
        return $this->msg;
    }

    //错误处理
    public function error(int $code, string $info, string $file, int $line): void
    {
        $error = $this->parseError($code, $info, $file, $line, 0);
        if ($code == E_NOTICE) {
            if (PHP_SAPI != 'cli' && APP_DEBUG && !APP_TRACE) {
                echo '<p style="color:#900">[ERROR] ' . $info . ' [' . basename($file) . ':' . $line . ']<p>';
            }
        } elseif (!in_array($code, [E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED])) {
            $this->output($error);
        } else {
            Log::init()->write($error['msg'], $error['type']);
        }
    }

    //异常处理
    public function exception(Throwable $exception): void
    {
        $code = $exception->getCode();
        $info = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $error = $this->parseError($code, $info, $file, $line);
        $this->output($error);
    }

    //格式化错误信息
    private function parseError(int $code, string $info, string $file, int $line, int $type = 1): array
    {
        $file = Dir::hideRoot($file);
        $error = [];
        $error['code'] = $code;
        $error['error'] = $info;
        $error['file'] = $file;
        $error['line'] = $line;
        $error['type'] = ($type == 1) ? 'EXCEPTION' : 'ERROR';
        $code = ($code != 0) ? '[' . $code . ']' : '';
        $error['msg'] = $error['type'] . $code . ': ' . $info . ' [' . $file . ':' . $line . ']';
        $this->msg[] = $error['msg'];
        return $error;
    }

    //错误输出
    public function output(array $error): void
    {
        if (PHP_SAPI == 'cli') {
            die(PHP_EOL . "\033[;36m " . $error['msg'] . " \x1B[0m\n" . PHP_EOL);
        }
        if (!APP_DEBUG || IS_AJAX) {
            Log::init()->write($error['msg'], $error['type']);
        }
        $msg = APP_DEBUG ? $error['error'] : '程序错误，请稍候访问...';
        ob_clean();
        if (IS_AJAX) {
            Response::json(500, $msg);
        } else {
            $tpl = APP_DEBUG ? 'error_show.php' : 'error_hide.php';
            include ROOT_PATH . '/willphp/tpl/' . $tpl;
        }
        exit();
    }
}