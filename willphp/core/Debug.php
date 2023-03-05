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

    protected array $items = ['sql' => [], 'debug' => [], 'error' => []];
    private string $path; //路由路径
    private float $runtime; //运行时间
    private string $memory; //内存开销
    private int $fileTotal; //加载文件数
    private string $filesize; //加载文件大小

    private function __construct()
    {
        $this->path = Route::init()->getPath();
        $this->runtime = round((microtime(true) - START_TIME), 4);
        $this->memory = number_format((memory_get_usage() - START_MEMORY) / 1024, 2);
        $files = get_included_files();
        $this->fileTotal = count($files);
        $filesize = 0;
        $level = get_config('debug.level', []);
        foreach ($files as $k => $file) {
            $k++;
            $fs = filesize($file);
            if (isset($level['file'])) {
                $this->items['file'][] = $k . '.' . $file . ' ( ' . number_format($fs / 1024, 2) . ' KB )';
            }
            $filesize += $fs;
        }
        $this->filesize = number_format($filesize / 1024, 2);

    }

    public function trace($msg, string $level = 'debug'): void
    {
        $level = strtolower($level);
        $level = !is_array($msg) && in_array($level, ['sql', 'error']) ? $level : 'debug';
        $this->items[$level][] = $msg;
    }

    public function appendTrace(string $content = ''): string
    {
        $trace = $this->getTrace();
        $pos = strripos($content, '</body>');
        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $trace . substr($content, $pos);
        } else {
            $content .= $trace;
        }
        return $content;
    }

    protected function getRunLog(): string
    {
        return '<script>console.log("Powered By ' . __POWERED__ . ' | path:' . $this->path . ' | files:' . $this->fileTotal . '(' . $this->filesize . 'KB) | memory:' . $this->memory . 'KB | runtime:' . $this->runtime . 's");</script>';
    }

    protected function getTrace(): string
    {
        if (!APP_TRACE || IS_AJAX) {
            return '';
        }
        $trace_show = get_config('debug.trace_show', true);
        if (!$trace_show) {
            return $this->getRunLog();
        }
        $end_time = $this->runtime;
        $trace = $this->parseTrace();
        $errno = '';
        if (!empty($this->items['error'])) {
            $errno = ' <span style="color:red">' . count($this->items['error']) . '</span>';
        }
        ob_start();
        include ROOT_PATH . '/willphp/core/view/trace.php';
        return "\n" . ob_get_clean() . "\n";
    }

    protected function parseTrace(): array
    {
        $level = get_config('debug.level', []);
        if (!isset($level['base'])) {
            $level['base'] = '基本';
        }
        $this->items['error'] = Error::init()->getError();
        $this->items['base']['主机信息'] = $_SERVER['SERVER_SOFTWARE'];
        $this->items['base']['请求信息'] = $_SERVER['SERVER_PROTOCOL'] . ' ' . $_SERVER['REQUEST_METHOD'] . ': <a href="' . __URL__ . '" style="color:#000;">' . __URL__ . '</a>';
        $this->items['base']['路由参数'] = $this->path;
        $this->items['base']['内存开销'] = $this->memory . ' KB <a href="' . __URL__ . '/api/clear">清除缓存</a>';
        $this->items['base']['调试统计'] = '文件：' . $this->fileTotal . '(' . $this->filesize . ' KB)';
        $this->items['base']['运行时间'] = $this->runtime . 's at ' . date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' <a href="http://www.113344.com" style="color:green;" target="_blank">WillPHP' . __VERSION__ . '</a>';
        $this->items['sql'] = $this->filter($this->items['sql'], []);
        $this->items['debug'] = $this->filter($this->items['debug'], []);
        $this->items['error'] = $this->filter($this->items['error'], []);
        if (isset($level['post'])) {
            $this->items['post'] = $this->filter($_POST, []);
        }
        if (isset($level['post'])) {
            $this->items['get'] = $this->filter($_GET, []);
        }
        if (isset($level['cookie'])) {
            $cookie = Cookie::init()->all();
            $this->items['cookie'] = $this->filter($cookie, []);
        }
        if (isset($level['session'])) {
            $session = Session::init()->all();
            $this->items['session'] = $this->filter($session, []);
        }
        $trace = [];
        foreach ($level as $k => $name) {
            $title = $name;
            $total = 0;
            if ($k != 'base') {
                $total = count($this->items[$k]);
                $title = $name . '(' . $total . ')';
            }
            if ($total > 0 || !in_array($k, ['post', 'get', 'cookie', 'session'])) {
                $trace[$title] = $this->items[$k];
            }
            if (!in_array($k, ['base', 'file']) && $total > 0) {
                $trace[$level['base']]['调试统计'] .= ' | ' . $name . '：' . $total;
            }
        }
        return $trace;
    }

    public function filter($data, $default = '')
    {
        if (empty($data)) {
            return $default;
        }
        if (is_array($data)) {
            array_walk_recursive($data, 'self::filterValue'); //输出前处理
        } else {
            self::filterValue($data, '');
        }
        return $data;
    }

    public static function filterValue(&$value, $key): void
    {
        $value = htmlspecialchars(strval($value), ENT_QUOTES);
    }
}