<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;

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

    public function error(int $code, string $info, string $file, int $line): void
    {
        $data = $this->parseError($code, $info, $file, $line);
        $this->setError($data['msg']);
        if ($code == E_NOTICE) {
            if (!IS_CLI && APP_DEBUG && !APP_TRACE) {
                echo '<p style="color:#900">[ERROR] ' . $info . ' [' . $data['file'] . ':' . $line . ']<p>';
            }
        } elseif (!in_array($code, [E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED])) {
            $this->showError($data);
        } else {
            Log::init()->write($data['msg'], $data['type']);
        }
    }

    public function exception(Throwable $exception): void
    {
        $code = $exception->getCode();
        $info = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $data = $this->parseError($code, $info, $file, $line, true);
        $this->setError($data['msg']);
        $this->showError($data);
    }

    protected function showError(array $data): void
    {
        if (IS_CLI) {
            die(PHP_EOL . "\033[;41m " . $data['msg'] . " \x1B[0m" . PHP_EOL);
        }
        if (!APP_DEBUG || IS_AJAX) {
            Log::init()->write($data['msg'], $data['type']);
        }
        $msg = APP_DEBUG ? $data['error'] : Config::init()->get('app.error_msg', 'Page error! Please try again later～');
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
        } else {
            $tpl = APP_DEBUG ? 'show' : 'hide';
            $error_page = APHP_TOP . '/aphp/tpl/error_' . $tpl . '.php';
            if (!is_file($error_page)) {
                echo $msg;
            } else {
                include $error_page;
            }
        }
        exit();
    }

    public function setError(string $msg): void
    {
        $this->errors[] = $msg;
    }

    public function getError(): array
    {
        return $this->errors;
    }

    protected function parseError(int $code, string $info, string $file, int $line, bool $isException = false): array
    {
        $data = [];
        $data['code'] = $code;
        $data['error'] = $info;
        $data['file'] = substr($file, strlen(APHP_TOP . '/'));
        $data['line'] = $line;
        $data['type'] = $isException ? 'EXCEPTION' : 'ERROR';
        $data['msg'] = $data['type'] . '[' . $code . ']: ' . $info . ' [' . $data['file'] . ':' . $line . ']';
        return $data;
    }
}