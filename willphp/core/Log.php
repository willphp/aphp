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
class Log
{
    use Single;

    protected string $dir;
    protected array $log = [];

    private function __construct()
    {
        $this->dir = Dir::make(RUNTIME_PATH . '/log', 0777);
    }

    public function record(string $message, string $level = 'ERROR'): void
    {
        $this->log[] = date('[ c ]') . $level . ':' . $message . PHP_EOL;
    }

    public function logVar($vars, string $name = 'var'): void
    {
        $this->record(json_encode($vars), $name);
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