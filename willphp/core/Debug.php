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

class Debug
{
    use Single;

    protected array $level;
    protected array $runInfo;
    protected array $items = ['sql' => [], 'debug' => [], 'error' => []];
    protected int $filesize = 0;

    private function __construct()
    {
        $this->level = Config::init()->get('debug.level', []);
        if (!isset($this->level['base'])) {
            $this->level['base'] = '基本';
        }
        $this->runInfo = $this->getRunInfo();
    }

    public function getRunLog(): string
    {
        $run = $this->runInfo;
        return '<!--Processed in '.$run['time'].', Memory '.$run['memory'].', '.count($this->items['sql']). ' queries, '. $run['total'] . ' files(' . $run['filesize'] . ')-->';
    }

    public function trace($msg, string $level = 'debug'): void
    {
        $level = strtolower($level);
        $level = !is_array($msg) && in_array($level, ['sql', 'error']) ? $level : 'debug';
        $this->items[$level][] = $msg;
    }

    public function appendTrace(string $content = ''): string
    {
        if (IS_AJAX || !APP_TRACE || Config::init()->get('debug.is_hide', false)) {
            return $content;
        }
        $html = $this->getTraceHtml();
        $pos = strripos($content, '</body>');
        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $html . substr($content, $pos);
        } else {
            $content .= $html;
        }
        return $content;
    }

    protected function getTraceHtml(): string
    {
        [$level, $trace] = $this->getTrace();
        $runtime = $this->runInfo['time'];
        $errorTotal = !empty($this->items['error']) ? count($this->items['error']) : '';
        ob_start();
        include ROOT_PATH . '/willphp/core/inc_tpl/debug_trace.php';
        return "\n" . ob_get_clean() . "\n";
    }

    protected function getTrace(): array
    {
        $level = $this->level;
        $trace = [];
        $trace['file'] = $this->runInfo['file'];
        if (isset($level['sql'])) {
            $trace['sql'] = array_map(fn($v):string=> $this->filter($v), $this->items['sql']);
        }
        if (isset($level['debug'])) {
            $trace['debug'] = array_map(fn($v): string => $this->filter($v), $this->items['debug']);
        }
        if (isset($level['post'])) {
            $trace['post'] = array_map(fn($v): string => $this->filter($v), $_POST);
        }
        if (isset($level['get'])) {
            $trace['get'] = array_map(fn($v): string => $this->filter($v), $_GET);
        }
        if (isset($level['cookie'])) {
            $trace['cookie'] = array_map(fn($v): string => $this->filter($v), Cookie::init()->all());
        }
        if (isset($level['session'])) {
            $session = Session::all();
            if (isset($session['_FLASH_'])) {
                unset($session['_FLASH_']);
            }
            $trace['session'] = array_map(fn($v): string => $this->filter($v), $session);
        }
        $trace['error'] = $this->items['error'];
        $totalExt = '';
        foreach ($level as $name => &$title) {
            if ($name != 'base') {
                $total = count($trace[$name]);
                if ($total > 0 && $name != 'file') {
                    $totalExt .=  ' | ' . $title . '：' . $total;
                }
                $title .= '('.$total.')';
                if ($total == 0 && !in_array($name, ['sql', 'error'])) {
                    unset($level[$name]);
                }
            }
        }
        $trace['base'] = [
            '主机信息：'.$_SERVER['SERVER_SOFTWARE'].' PHP版本：'.PHP_VERSION,
            '请求信息：'.$_SERVER['SERVER_PROTOCOL'] . ' ' . $_SERVER['REQUEST_METHOD'] . ': <a href="' . __URL__ . '" style="color:#333;">' . __URL__ . '</a>',
            '路由参数：'.$this->runInfo['path'],
            '内存开销：'.$this->runInfo['memory']. ' <a href="' . __URL__ . '/api/clear" style="color:green;">清除缓存</a>',
            '调试统计：文件：' . $this->runInfo['total'] . '(' . $this->runInfo['filesize'] . ')'.$totalExt,
            '运行时间：'.$this->runInfo['time'] . ' at ' . date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' <a href="http://www.113344.com" style="color:green;" target="_blank" rel="noopenner noreferrer">WillPHP' . __VERSION__ . '</a>',
        ];
        $list = [];
        foreach ($level as $k => $v) {
            $list[$k] = $trace[$k];
        }
        return [$level, $list];
    }

    protected function filter($value): string
    {
        if (is_scalar($value)) {
            return htmlspecialchars(strval($value), ENT_QUOTES);
        }
        return '<pre>'.htmlspecialchars(var_export($value, true), ENT_QUOTES).'</pre>';
    }

    public function getRunInfo(): array
    {
        $trace = [];
        $trace['path'] = Route::init()->getPath();
        $trace['time'] = round((microtime(true) - START_TIME), 4) . ' s';
        $trace['memory'] = Dir::sizeFormat(memory_get_usage() - START_MEMORY); //number_format((memory_get_usage() - START_MEMORY) / 1024, 2) . ' KB';
        $files = get_included_files();
        $trace['file'] = array_map(fn(string $file): string => $this->getIncFile($file), $files);
        $trace['total'] = count($files);
        $trace['filesize'] = number_format($this->filesize / 1024, 2) . ' KB';
        return $trace;
    }

    private function getIncFile(string $file): string
    {
        $filesize = filesize($file);
        $this->filesize += $filesize;
        return Dir::removeRoot($file) . '(' . number_format($filesize / 1024, 2) . ' KB)';
    }
}