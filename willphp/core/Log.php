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

use Exception;

class Log
{
    use Single;

    protected string $dir;
    protected array $log = [];

    private function __construct()
    {
        $this->dir(RUNTIME_PATH . '/log');
    }

    public function dir(string $dir): object
    {
        if (!dir_create($dir)) throw new Exception('日志目录创建失败或不可写');
        $this->dir = $dir;
        return $this;
    }

    public function record($message, $level = 'ERROR'): bool
    {
        $this->log[] = date('[ c ]') . $level . ':' . $message . PHP_EOL;
        return true;
    }

    public function write($message, $level = 'ERROR'): bool
    {
        $file = $this->dir . '/' . date('Y_m_d') . '.log';
        return error_log(date('[ c ]') . $level . ':' . $message . PHP_EOL, 3, $file, null);
    }

    public function __destruct()
    {
        if (!empty($this->log)) {
            $file = $this->dir . '/' . date('Y_m_d') . '.log';
            error_log(implode('', $this->log), 3, $file, null);
        }
    }
}