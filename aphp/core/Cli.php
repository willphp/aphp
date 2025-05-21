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
 * 命令行运行类
 */
class Cli
{
    public static function run(string $uri, string $app = '', bool $isCall = false)
    {
        if (empty($app)) {
            $app = APP_NAME;
        }
        $uri = empty($uri) ? ['help:cli'] : explode(' ', $uri);
        $cmd = explode(':', array_shift($uri));
        $method = $cmd[1] ?? 'cli'; // 方法
        $isCmd = true; // 是否命令
        $className = name_to_camel($cmd[0]); // 类名
        $class = 'aphp\\cli\\' . $className;
        if (!method_exists($class, $method)) {
            $class = 'app\\' . $app . '\\command\\' . $className;
            if (!method_exists($class, $method)) {
                $class = 'app\\' . $app . '\\controller\\' . $className;
                $isCmd = false;
            }
        }
        if (method_exists($class, $method)) {
            if (!$isCall && !defined('CLI_COMMAND')) {
                define('CLI_COMMAND', $class . ':' . $method);
            }
            $args = []; // 实参
            $_param = []; // 形参
            if (empty(!$uri)) {
                foreach ($uri as $k => $v) {
                    [$k, $v] = split_prefix_name($v, strval($k), ':');
                    if (!str_starts_with($v, '-')) { // 去除形参
                        $args[$k] = $v;
                    } else {
                        $_param[] = $v;
                    }
                }
                Filter::init()->input($args); // 过滤输入
            }
            $obj = $isCmd ? call_user_func_array([$class, 'init'], [$isCall]) : App::make($class);
            if (empty($args)) {
                $res = $obj->$method();
            } else {
                if (in_array('-p', $_param)) {
                    $res = call_user_func_array([$obj, $method], $args);
                } else {
                    $args = array_merge($args, $_param);
                    $res = $obj->$method($args);
                }
            }
            if ($isCall) {
                return $res;
            }
            Response::output($res, APP_TRACE);
            return true;
        } elseif (!$isCall) {
            Response::halt('', 404, ['path' => $class . ':' . $method]);
        }
        return false;
    }
}