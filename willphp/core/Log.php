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
/**
 * 日志记录类
 */
class Log
{
    use Single;

    protected string $dir; //日志存放目录
    protected array $log = []; //日志记录

    //初始化存放目录
    private function __construct()
    {
        $this->dir = Dir::make(RUNTIME_PATH . '/log', 0777);
    }

    //将变量值写入日志(调试变量时使用)
    public function value($vars, string $name = 'var'): void
    {
        $this->record(json_encode($vars), $name);
    }

    //记录日志
    public function record(string $message, string $level = 'ERROR'): void
    {
        $this->log[] = date('[ c ]') . $level . ':' . $message . PHP_EOL;
    }

    //写入日志
    public function write(string $message, string $level = 'ERROR'): bool
    {
        return error_log(date('[ c ]') . $level . ':' . $message . PHP_EOL, 3, $this->dir . '/' . date('Y_m_d') . '.log');
    }

    //结束时写入日志记录
    public function __destruct()
    {
        if (!empty($this->log)) {
            error_log(implode('', $this->log), 3, $this->dir . '/' . date('Y_m_d') . '.log');
        }
    }
}