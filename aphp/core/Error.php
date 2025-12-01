<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;

use Throwable;

/**
 * 错误处理类
 */
class Error
{
    use Single;

    protected array $errors = []; // 错误信息

    private function __construct()
    {
        error_reporting(0);
        set_error_handler([$this, 'error']);
        set_exception_handler([$this, 'exception']);
    }

    // 设置错误信息
    public function setError(string $msg): void
    {
        $this->errors[] = $msg;
    }

    // 获取错误信息
    public function getError(): array
    {
        return $this->errors;
    }

    // 错误处理
    public function error(int $code, string $info, string $file, int $line): void
    {
        $data = [];
        $data['code'] = $code;
        $data['error'] = $info;
        $data['file'] = substr($file, strlen(ROOT_PATH . '/'));
        $data['line'] = $line;
        $data['type'] = 'ERROR';
        $data['msg'] = $data['type'] . '[' . $code . ']: ' . $info . ' [' . $data['file'] . ':' . $line . ']';
        $this->errors[] = $data['msg'];
        if ($code == E_NOTICE) {
            if (!IS_CLI && APP_DEBUG && !APP_TRACE) {
                echo '<p style="color:#900">[ERROR] ' . $info . ' [' . $data['file'] . ':' . $line . ']<p>';
            }
        } elseif (!in_array($code, [E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED])) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_shift($trace);
            $data['trace'] = $this->_format_trace($trace);
            $this->_show_error($data);
        } else {
            Log::init()->write($data['msg'], $data['type']);
        }
    }

    // 异常处理
    public function exception(Throwable $exception): never
    {
        $data = [];
        $data['code'] = $exception->getCode();
        $data['error'] = $exception->getMessage();
        $data['file'] = substr($exception->getFile(), strlen(ROOT_PATH . '/'));
        $data['line'] = $exception->getLine();
        $data['type'] = 'EXCEPTION';
        $data['msg'] = $data['type'] . '[' . $data['code'] . ']: ' . $data['error'] . ' [' . $data['file'] . ':' . $data['line'] . ']';
        $this->errors[] = $data['msg'];
        $data['trace'] = $this->_format_trace($exception->getTrace());
        $this->_show_error($data);
    }

    // 错误显示
    protected function _show_error(array $data): never
    {
        if (IS_CLI) {
            exit(PHP_EOL . "\033[;41m " . $data['msg'] . " \x1B[0m\n" . PHP_EOL);
        }
        if (!APP_DEBUG || IS_AJAX) {
            Log::init()->write($data['msg'], $data['type']);
        }
        $is_install = str_contains($_SERVER['REQUEST_URI'], 'install'); // 是否为安装时
        $msg = APP_DEBUG || $is_install ? $data['error'] : Config::init()->get('app.error_msg', 'Page error! Please try again later～');
        ob_clean();
        if (IS_AJAX) {
            header('Content-type: application/json; charset=utf-8');
            $json = Config::init()->get('response.json', ['ret' => 'ret', 'msg' => 'msg', 'data' => 'data', 'status' => 'status']);
            $res = [
                $json['ret'] => 500,
                $json['msg'] => $msg,
                $json['status'] => 0
            ];
            exit(json_encode($res, JSON_UNESCAPED_UNICODE));
        }
        $tpl = APP_DEBUG ? 'show' : 'hide';
        $error_tpl = ROOT_PATH . '/aphp/tpl/error_' . $tpl . '.php';
        if (!is_file($error_tpl)) {
            echo $msg;
        } else {
            include $error_tpl;
        }
        exit();
    }

    // 格式化trace
    protected function _format_trace(array $trace): array
    {
        $trace = array_reverse($trace);
        $data = [];
        foreach ($trace as $i => $frame) {
            $str = '';
            if (isset($frame['file'])) {
                $str .= substr($frame['file'], strlen(ROOT_PATH . '/')) . '(' . $frame['line'] . '): ';
            }
            if (isset($frame['class'])) {
                $str .= $frame['class'] . $frame['type'];
            }
            $str .= $frame['function'] . '()';
            $data[$i] = $str;
        }
        return $data;
    }
}