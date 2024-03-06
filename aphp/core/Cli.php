<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
class Cli
{
    public static function run(string $uri = '', string $app = APP_NAME, bool $isCall = false)
    {
        $uri = empty($uri) ? ['make:cli'] : explode(' ', $uri);
        $cmd = explode(':', array_shift($uri));
        $action = $cmd[1] ?? 'cli';
        $isCmd = true;
        $class = 'aphp\\cli\\' . name_camel($cmd[0]);
        if (!method_exists($class, $action)) {
            $class = 'app\\' . $app . '\\command\\' . name_camel($cmd[0]);
            if (!method_exists($class, $action)) {
                $class = 'app\\' . $app . '\\controller\\' . name_camel($cmd[0]);
                $isCmd = false;
            }
        }
        if (method_exists($class, $action)) {
            defined('CLI_COMMAND') or define('CLI_COMMAND', $class . ':' . $action);
            $args = [];
            if (empty(!$uri)) {
                foreach ($uri as $k => $v) {
                    [$k, $v] = parse_prefix_name($v, strval($k), ':');
                    $args[$k] = $v;
                }
                Filter::init()->input($args);
            }
            $obj = $isCmd ? call_user_func_array([$class, 'init'], [$isCall]) : App::make($class);
            $res = empty($args) ? $obj->$action() : $obj->$action($args);
            if ($isCall) {
                return $res;
            }
            Response::output($res, APP_TRACE);
            return true;
        } elseif (!$isCall) {
            Response::halt('', 404, ['path' => $class. ':' . $action]);
        }
        return false;
    }
}