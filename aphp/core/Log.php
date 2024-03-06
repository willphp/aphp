<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
class Log
{
    use Single;

    protected string $dir;
    protected array $log = [];

    private function __construct()
    {
        $this->dir = Tool::dir_init(RUNTIME_PATH . '/log', 0777);
    }

    public function value($vars, string $name = 'var'): void
    {
        $this->record(json_encode($vars), $name);
    }

    public function record(string $message, string $level = 'ERROR'): void
    {
        $this->log[] = date('[ c ]') . $level . ':' . $message . PHP_EOL;
    }

    public function write(string $message, string $level = 'ERROR'): bool
    {
        return error_log(date('[ c ]') . $level . ':' . $message . PHP_EOL, 3, $this->dir . '/' . date('Y_m_d') . '.log');
    }

    public function __destruct()
    {
        if (!empty($this->log)) {
            error_log(implode('', $this->log), 3, $this->dir . '/' . date('Y_m_d') . '.log');
        }
    }
}