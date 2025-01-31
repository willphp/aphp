<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\cli;
/**
 * 命令基类
 */
abstract class Command
{
    protected static object $single; // 单例实例
    protected bool $isCall = false;

    private function __construct(bool $isCall = false)
    {
        $this->isCall = $isCall;
    }

    // 禁止克隆
    private function __clone()
    {
    }

    // 获取单例实例，不存在创建
    public static function init(...$args): object
    {
        static $class = [];
        if (empty($args)) {
            $sign = md5(static::class);
            $class[$sign] ??= new static();
        } else {
            $sign = md5(serialize($args) . static::class);
            $class[$sign] ??= new static(...$args);
        }
        return static::$single = $class[$sign];
    }

    abstract public function cli();

    protected function success(string $msg = ''): ?bool
    {
        if (!$this->isCall) {
            if (empty($msg)) $msg = 'success!';
            die(PHP_EOL . "\033[;36m $msg \x1B[0m" . PHP_EOL . "\n");
        }
        return true;
    }

    protected function error(string $msg = ''): ?bool
    {
        if (!$this->isCall) {
            if (empty($msg)) $msg = 'fail!';
            die(PHP_EOL . "\033[;41m $msg \x1B[0m" . PHP_EOL . "\n");
        }
        return false;
    }
}