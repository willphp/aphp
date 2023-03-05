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
class Response
{

    public static function validate(array $errors = []): void
    {
        if (!empty($errors)) {
            $msg = current($errors);
            if ($msg == 'not_msg') {
                $msg = '';
            }
            $param = ['field' => array_key_first($errors)];
            self::halt($msg, 406, $param);
        }
    }

    public static function getCodeMsg($msg = '', int $code = 400, array $params = []): string
    {
        if (is_array($msg)) {
            $msg = current($msg);
        }
        if (empty($msg)) {
            $codeMsg = get_config('response.msg', []);
            $msg = $codeMsg[$code] ?? '出错了！';
        }
        if (!empty($params)) {
            $msg = preg_replace_callback('/{\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*}/i', fn($v) => $params[$v[1]] ?? '', $msg);
        }
        return $msg;
    }

    public static function halt($msg = '', int $code = 400, array $params = []): void
    {
        $msg = self::getCodeMsg($msg, $code, $params);
        if (PHP_SAPI == 'cli') {
            die(PHP_EOL . "\033[;36m " . $code . ':' . $msg . " \x1B[0m\n" . PHP_EOL);
        }
        if (IS_AJAX) {
            self::json($code, $msg);
        }
        $action = '_' . $code;
        $class = 'app\\' . APP_NAME . '\\controller\\Error';
        if (class_exists($class)) {
            $handler = App::make($class);
            $res = call_user_func_array([$handler, $action], [$msg, $params]);
            self::output($res);
        } else {
            include ROOT_PATH . '/willphp/core/view/500.php';
        }
        exit;
    }

    public static function output($res = null, bool $trace = false): void
    {
        if (is_object($res) && method_exists($res, '__toString')) {
            $res = $res->__toString();
        }
        if (is_scalar($res)) {
            if (preg_match('/^http(s?):\/\//', $res)) {
                header('location:' . $res);
            } else {
                if ($trace) {
                    $res = Debug::init()->appendTrace($res);
                }
                echo $res;
            }
        } elseif (is_null($res)) {
            return;
        } else {
            header('Content-type: application/json;charset=utf-8');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    public static function json(int $code, string $msg = '', ?array $data = null, array $extend = []): void
    {
        header('Content-type: application/json;charset=utf-8');
        $msg = self::getCodeMsg($msg, $code);
        $json = get_config('response.json', ['ret' => 'ret', 'msg' => 'msg', 'data' => 'data', 'status' => 'status']);
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

}