<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
class DebugBar
{
    use Single;

    protected array $tabs; //调试栏选项
    protected array $trace = []; //追踪信息
    protected array $items = ['debug' => [], 'error' => [], 'sql' => []]; //数据信息
    protected int $filesize = 0; //统计文件大小

    private function __construct()
    {
        $this->tabs = Config::init()->get('debug_bar.tabs', []);
        if (!isset($this->tabs['base'])) {
            $this->tabs['base'] = '基本';
        }
        $this->trace = $this->getTrace();
        $this->items['error'] = Error::init()->getError();
    }

    //获取追踪信息
    public function getTrace(): array
    {
        $trace = [];
        $trace['path'] = Route::init()->getPath();
        $trace['time'] = round((microtime(true) - START_TIME), 4) . ' s';
        $trace['memory'] = Tool::size2kb(memory_get_usage() - START_MEMORY);
        $files = get_included_files();
        $trace['file'] = array_map(fn(string $file): string => $this->formatFileName($file), $files);
        $trace['total'] = count($files);
        $trace['filesize'] = number_format($this->filesize / 1024, 2) . ' KB';
        return $trace;
    }

    //记录信息到调试栏
    public function trace($msg, string $type = 'debug'): void
    {
        $type = strtolower($type);
        $type = !is_array($msg) && in_array($type, ['sql', 'error']) ? $type : 'debug';
        $this->items[$type][] = $msg;
    }

    //获取html脚注
    public function getHtmlFooter(): string
    {
        $trace = $this->trace;
        return '<!--Processed in ' . $trace['time'] . ', Memory ' . $trace['memory'] . ', ' . count($this->items['sql']) . ' queries, ' . $trace['total'] . ' files(' . $trace['filesize'] . ')-->';
    }

    //格式化文件名(大小)
    private function formatFileName(string $file): string
    {
        $filesize = filesize($file);
        $this->filesize += $filesize;
        return substr($file, strlen(APHP_TOP.'/')) . '(' . number_format($filesize / 1024, 2) . ' KB)';
    }

    //添加调试栏html到页面内容后
    public function appendDebugBar(string $content = ''): string
    {
        if (IS_AJAX || !APP_TRACE || Config::init()->get('debug_bar.is_hide', false)) {
            return $content;
        }
        $html = $this->getDebugBarHtml();
        $pos = strripos($content, '</body>');
        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $html . substr($content, $pos);
        } else {
            $content .= $html;
        }
        return $content;
    }

    //获取调试栏html
    protected function getDebugBarHtml(): string
    {
        [$tabs, $trace] = $this->parseTrace();
        $runtime = $this->trace['time']; //运行时间
        $errors = !empty($this->items['error']) ? count($this->items['error']) : ''; //错误统计
        ob_start();
        include APHP_TOP . '/aphp/tpl/debug_trace.php';
        return "\n" . ob_get_clean() . "\n";
    }

    protected function parseTrace(): array
    {
        $tabs = $this->tabs;
        $trace = [];
        $trace['file'] = $this->trace['file'];
        if (isset($tabs['sql'])) {
            $trace['sql'] = array_map(fn($v): string => $this->filter($v), $this->items['sql']);
        }
        if (isset($tabs['debug'])) {
            $trace['debug'] = array_map(fn($v): string => $this->filter($v), $this->items['debug']);
        }
        if (isset($tabs['post'])) {
            $trace['post'] = array_map(fn($v): string => $this->filter($v), $_POST);
        }
        if (isset($tabs['get'])) {
            $trace['get'] = array_map(fn($v): string => $this->filter($v), $_GET);
        }
        if (isset($tabs['cookie'])) {
            $trace['cookie'] = array_map(fn($v): string => $this->filter($v), Cookie::init()->all());
        }
        if (isset($tabs['session'])) {
            $session = Session::init()->all();
            if (isset($session['_FLASH_'])) {
                unset($session['_FLASH_']);
            }
            $trace['session'] = array_map(fn($v): string => $this->filter($v), $session);
        }
        $trace['error'] = $this->items['error'];
        $totalExtend = '';
        foreach ($tabs as $name => &$title) {
            if ($name != 'base') {
                $total = count($trace[$name]);
                if ($total > 0 && $name != 'file') {
                    $totalExtend .= ' | ' . $title . ': ' . $total;
                }
                $title .= '(' . $total . ')';
                if ($total == 0 && !in_array($name, ['sql', 'error'])) {
                    unset($tabs[$name]);
                }
            }
        }
        $trace['base'] = [
            '主机信息：' . $_SERVER['SERVER_SOFTWARE'] . ' PHP版本：' . PHP_VERSION,
            '请求信息：' . $_SERVER['SERVER_PROTOCOL'] . ' ' . $_SERVER['REQUEST_METHOD'] . '：<a href="' . __URL__ . '" style="color:#333;">' . __URL__ . '</a>',
            '路由参数：' . $this->trace['path'],
            '内存开销：' . $this->trace['memory'] . ' <a href="' . Route::init()->buildUrl('api/clear') . '" style="color:green;">清除缓存</a>',
            '调试统计：文件_' . $this->trace['total'] . '(' . $this->trace['filesize'] . ')' . $totalExtend,
            '运行时间：' . $this->trace['time'] . ' at ' . date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' <a href="https://www.aphp.top" style="color:green;" target="_blank" rel="noopenner noreferrer">APHP' . __VERSION__ . '</a>',
        ];
        $list = [];
        foreach ($tabs as $k => $v) {
            $list[$k] = $trace[$k];
        }
        return [$tabs, $list];
    }

    //格式化变量输出
    protected function filter($value): string
    {
        if (is_scalar($value)) {
            return htmlspecialchars(strval($value), ENT_QUOTES);
        }
        return '<pre>' . htmlspecialchars(var_export($value, true), ENT_QUOTES) . '</pre>';
    }
}