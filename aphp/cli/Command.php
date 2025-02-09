<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\cli;

use aphp\core\Single;

/**
 * 命令基类
 */
abstract class Command
{
    use Single;

    protected bool $isCall;

    private function __construct(bool $isCall = false)
    {
        $this->isCall = $isCall;
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