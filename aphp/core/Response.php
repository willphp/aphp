<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
class Response
{
    public static function output($res = null, bool $trace = false): void
    {
        if (is_null($res)) {
            return;
        }
        if (is_object($res) && method_exists($res, '__toString')) {
            $res = $res->__toString();
        }
        if (is_scalar($res)) {
            if (!IS_CLI && preg_match('/^http(s?):\/\//', $res)) {
                header('location:' . $res);
            } else {
                if (!IS_CLI) {
                    header('Content-type: text/html; charset=utf-8');
                    if ($trace) {
                        $res = DebugBar::init()->appendDebugBar($res);
                    }
                    if (Config::init()->get('debug_bar.is_show_foot', false)) {
                        $res .= "\n".DebugBar::init()->getHtmlFooter();
                    }
                } elseif (is_bool($res)) {
                    $res = '';
                }
                echo $res;
                if (IS_CLI && $trace) {
                    echo "\n[".CLI_COMMAND."]";
                }
            }
        } else {
            header('Content-type: application/json; charset=utf-8');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    public static function json(int $code, string $msg = '', ?array $data = null, array $extend = []): void
    {
        header('Content-type: application/json; charset=utf-8');
        if (empty($msg)) {
            $msg = Config::init()->get('response.code_msg.' . $code, 'Error...');
        }
        $json = Config::init()->get('response.json', ['ret' => 'ret', 'msg' => 'msg', 'data' => 'data', 'status' => 'status']);
        $res = [];
        $res[$json['ret']] = $code;
        $res[$json['msg']] = $msg;
        if (null !== $data) {
            $res[$json['data']] = $data;
        }
        $res[$json['status']] = ($code < 400) ? 1 : 0;
        $res = array_merge($res, $extend);
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    public static function halt(string $msg = '', int $code = 400, array $params = []): void
    {
        if (empty($msg)) {
            $msg = Config::init()->get('response.code_msg.' . $code, 'Error...');
        }
        if (!empty($params) && str_contains($msg, '$')) {
            $msg = preg_replace_callback('/{\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*}/i', fn($v) => $params[$v[1]] ?? '', $msg);
        }
        if (IS_CLI) {
            die(PHP_EOL . "\033[;36m " . $code . ': ' . $msg . " \x1B[0m\n" . PHP_EOL);
        }
        if (IS_AJAX) {
            self::json($code, $msg);
        }
        $class = '\\app\\'. APP_NAME. '\\controller\\Error';
        if (class_exists($class)) {
            $res = call_user_func_array([App::make($class), '_' . $code], [$msg, $params]);
            self::output($res);
        } else {
            ob_clean();
            header('Content-type: text/html; charset=utf-8');
            include APHP_TOP . '/aphp/tpl/response_halt.php';
        }
        exit;
    }

    public static function validate(array $errors = []): void
    {
        if (!empty($errors)) {
            $msg = current($errors);
            self::halt($msg, 406, ['field' => array_key_first($errors)]);
        }
    }
}