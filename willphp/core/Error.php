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

class Error
{
    use Single;

    protected array $errors = [];

    private function __construct()
    {
        error_reporting(0);
        set_error_handler([$this, 'error'], E_ALL | E_STRICT);
        set_exception_handler([$this, 'exception']);
    }

    public function error(int $code, string $error, string $file, int $line): void
    {
        $err = [];
        $err['type'] = 'ERROR';
        $err['code'] = $code;
        $err['file'] = $file;
        $err['line'] = $line;
        $err['error'] = $error;
        $err['msg'] = 'ERROR: [' . $code . ']' . $error . '[' . $file . ':' . $line . ']';
        $this->errors[] = $err['msg'];
        if ($code == E_NOTICE) {
            if (PHP_SAPI != 'cli' && APP_DEBUG && !APP_TRACE) {
                echo '<p style="color:#900">[' . $err['type'] . '] ' . $error . ' [' . basename($file) . ':' . $line . ']<p>';
            }
        } elseif (!in_array($code, [E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED])) {
            $this->showError($err);
        }
    }

    public function exception(Throwable $exception): void
    {
        $err = [];
        $err['type'] = 'EXCEPTION';
        $err['code'] = $exception->getCode();
        $err['file'] = $exception->getFile();
        $err['line'] = $exception->getLine();
        $err['error'] = $exception->getMessage();
        $err['msg'] = 'EXCEPTION:' . $err['error'] . '[' . $err['file'] . ':' . $err['line'] . ']';
        $this->errors[] = $err['msg'];
        $this->showError($err);
    }

    public function getError(): array
    {
        return $this->errors;
    }

    public function showError(array $err): void
    {
        if (PHP_SAPI == 'cli') {
            die(PHP_EOL . "\033[;36m " . $err['msg'] . " \x1B[0m\n" . PHP_EOL);
        }
        if (!APP_DEBUG || IS_AJAX) {
            Log::init()->write($err['msg'], $err['type']); //写入日志
        }
        $msg = APP_DEBUG ? $err['error'] : get_config('response.msg.500', '系统错误，请稍候访问');
        ob_clean();
        if (IS_AJAX) {
            Response::json(500, $msg);
        } elseif (APP_DEBUG) {
            include ROOT_PATH . '/willphp/core/view/error.php';
        } else {
            include ROOT_PATH . '/willphp/core/view/500.php';
        }
        die;
    }
}