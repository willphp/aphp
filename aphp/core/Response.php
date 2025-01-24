<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
/**
 * 响应类
 */
class Response
{
    // 输出响应
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
                    if (Config::init()->get('debug_bar.show_html_footer', false)) {
                        $res .= "\n" . DebugBar::init()->getHtmlFooter();
                    }
                } elseif (is_bool($res)) {
                    $res = '';
                }
                echo $res;
                if (IS_CLI && $trace) {
                    echo "\n[Command: " . CLI_COMMAND . "]\n";
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
        $class = '\\app\\' . APP_NAME . '\\controller\\Error';
        if (class_exists($class)) {
            $res = call_user_func_array([App::make($class), '_' . $code], [$msg, $params]);
            self::output($res);
        } else {
            ob_clean();
            header('Content-type: text/html; charset=utf-8');
            $halt_file = ROOT_PATH . '/aphp/tpl/response_halt.php';
            if (file_exists($halt_file)) {
                include $halt_file;
            } else {
                echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no"><title>Error!</title></head><body><h1>):</h1><p style="color:#c00;">' . $msg . '</p><p><a href="javascript:history.back(-1);">Go Back</a></p></body></html>';
            }
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