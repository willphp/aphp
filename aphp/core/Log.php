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
 * 日志处理类
 */
class Log
{
    use Single;

    protected array $items = [];
    protected string $dir;

    private function __construct()
    {
        $this->dir = dir_init(RUNTIME_PATH . '/log', 0777);
    }

    // 记录日志
    public function record(string $msg, string $level = 'ERROR'): void
    {
        $this->items[] = date('[ c ]') . $level . ':' . $msg . PHP_EOL;
    }

    // 写入日志
    public function write(string $msg, string $level = 'ERROR'): bool
    {
        return error_log(date('[ c ]') . $level . ':' . $msg . PHP_EOL, 3, $this->dir . '/' . date('Y_m_d') . '.log');
    }

    // 记录打印变量
    public function dump($vars, string $name = 'var'): void
    {
        $this->record(json_encode($vars), $name);
    }

    public function __destruct()
    {
        if (!empty($this->items) && is_dir($this->dir)) {
            error_log(implode('', $this->items), 3, $this->dir . '/' . date('Y_m_d') . '.log');
        }
    }
}