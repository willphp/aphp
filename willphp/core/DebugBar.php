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
 * 调试栏类
 */
class DebugBar
{
    use Single;

    protected array $tabs; //调试栏选项设置
    protected array $trace = []; //追踪信息
    protected array $items = ['debug' => [], 'error' => [], 'sql' => []]; //栏目数据信息
    protected int $filesize = 0; //用于统计文件大小

    private function __construct()
    {
        $this->tabs = Config::init()->get('debugbar.tabs', []);
        if (!isset($this->tabs['base'])) {
            $this->tabs['base'] = '基本';
        }
        $this->trace = $this->getTraceInfo();
    }

    //记录信息到调试栏
    public function trace($msg, string $type = 'debug'): void
    {
        $type = strtolower($type);
        $type = !is_array($msg) && in_array($type, ['sql', 'error']) ? $type : 'debug';
        $this->items[$type][] = $msg;
    }

    //获取html脚注
    public function getFootnote(): string
    {
        $trace = $this->trace;
        return '<!--Processed in ' . $trace['time'] . ', Memory ' . $trace['memory'] . ', ' . count($this->items['sql']) . ' queries, ' . $trace['total'] . ' files(' . $trace['filesize'] . ')-->';
    }

    //获取追踪信息
    public function getTraceInfo(): array
    {
        $trace = [];
        $trace['path'] = Route::init()->getPath();
        $trace['time'] = round((microtime(true) - START_TIME), 4) . ' s';
        $trace['memory'] = Dir::sizeFormat(memory_get_usage() - START_MEMORY); //number_format((memory_get_usage() - START_MEMORY) / 1024, 2) . ' KB';
        $files = get_included_files();
        $trace['file'] = array_map(fn(string $file): string => $this->formatFileName($file), $files);
        $trace['total'] = count($files);
        $trace['filesize'] = number_format($this->filesize / 1024, 2) . ' KB';
        return $trace;
    }

    //格式化文件名(大小)
    private function formatFileName(string $file): string
    {
        $filesize = filesize($file);
        $this->filesize += $filesize;
        return Dir::hideRoot($file) . '(' . number_format($filesize / 1024, 2) . ' KB)';
    }

    //添加调试栏html到页面内容后
    public function appendDebugbar(string $content = ''): string
    {
        if (IS_AJAX || !APP_TRACE || Config::init()->get('debugbar.is_hide', false)) {
            return $content;
        }
        $html = $this->getDebugbarHtml();
        $pos = strripos($content, '</body>');
        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $html . substr($content, $pos);
        } else {
            $content .= $html;
        }
        return $content;
    }

    //获取调试栏html
    protected function getDebugbarHtml(): string
    {
        [$tabs, $trace] = $this->getTrace();
        $runtime = $this->trace['time']; //运行时间
        $errors = !empty($this->items['error']) ? count($this->items['error']) : ''; //错误统计
        ob_start();
        include ROOT_PATH . '/willphp/tpl/debugbar_trace.php';
        return "\n" . ob_get_clean() . "\n";
    }

    protected function getTrace(): array
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
                    $totalExtend .= ' | ' . $title . '：' . $total;
                }
                $title .= '(' . $total . ')';
                if ($total == 0 && !in_array($name, ['sql', 'error'])) {
                    unset($tabs[$name]);
                }
            }
        }
        $trace['base'] = [
            '主机信息：' . $_SERVER['SERVER_SOFTWARE'] . ' PHP版本：' . PHP_VERSION,
            '请求信息：' . $_SERVER['SERVER_PROTOCOL'] . ' ' . $_SERVER['REQUEST_METHOD'] . ': <a href="' . __URL__ . '" style="color:#333;">' . __URL__ . '</a>',
            '路由参数：' . $this->trace['path'],
            '内存开销：' . $this->trace['memory'] . ' <a href="' . __URL__ . '/api/clear" style="color:green;">清除缓存</a>',
            '调试统计：文件：' . $this->trace['total'] . '(' . $this->trace['filesize'] . ')' . $totalExtend,
            '运行时间：' . $this->trace['time'] . ' at ' . date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' <a href="http://www.113344.com" style="color:green;" target="_blank" rel="noopenner noreferrer">WillPHP' . __VERSION__ . '</a>',
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