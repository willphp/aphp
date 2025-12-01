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
 * 分页类
 */
class Pagination
{
    use Single;

    private int $totalRow; // 总记录数
    private int $pageSize; // 每页记录数
    private int $showNum; // 显示页码数
    private int $totalPage; // 总页数
    private string $getVar; // 分页变量
    private array $unsetGetVar; // 不需要保留的get参数
    private int $currentNum; // 当前页码
    private int $offset; // 开始数
    private array $options = ['home' => 'home', 'end' => 'end', 'up' => 'Previous', 'down' => 'Next', 'pre' => '&laquo;', 'next' => '&raquo;', 'header' => 'records', 'unit' => 'page', 'theme' => 0];
    private array $search = ['%total%', '%header%', '%current%', '%pages%', '%unit%', '%home%', '%up%', '%pre%', '%number%', '%next%', '%down%', '%end%'];
    private string $html = '[%total% %header%] [%current%/%pages% %unit%] %home% %up% %pre% %number% %next% %down% %end%';

    private function __construct(int $totalRow, int $pageSize = 0, int $showNum = 0, string $getVar = '')
    {
        $config = Config::init()->get('pagination', []);
        $this->getVar = $getVar ?: $config['page_var'] ?: 'p';
        $this->unsetGetVar = $config['unset_get_var'] ?: [];
        $this->html = $config['page_html'] ?: $this->html;
        if (isset($config['options'])) {
            $this->options = array_merge($this->options, $config['options']);
        }
        $this->totalRow = $totalRow;
        $this->pageSize = $pageSize ?: $config['page_size'] ?: 10;
        $this->showNum = $showNum ?: $config['show_num'] ?: 5;
        $this->totalPage = (int)ceil($this->totalRow / $this->pageSize);
        $this->currentNum = $this->getCurrentNum();
        $this->offset = $this->pageSize * ($this->currentNum - 1);
    }

    public function set(string|array $name, mixed $value = ''): void
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                if (isset($this->options[$k])) {
                    $this->options[$k] = $v;
                }
            }
        } elseif (!empty($value)) {
            if ($name == 'html') {
                $this->html = $value;
            } elseif (isset($this->options[$name])) {
                $this->options[$name] = $value;
            }
        }
    }

    public function __toString(): string
    {
        return $this->getHtml();
    }

    public function getAttr(string $type = '')
    {
        $attr = [];
        $attr['total'] = $this->totalRow; // 总记录数
        $attr['current'] = $this->currentNum; // 当前页码
        $attr['offset'] = $this->offset; // 开始数
        $attr['page_size'] = $this->pageSize; // 每页记录数
        $attr['page_count'] = $this->totalPage; // 总页数
        if (empty($type)) {
            return $attr;
        }
        return $attr[$type] ?? 0;
    }

    public function getLimit(): string
    {
        return $this->offset . ',' . $this->pageSize;
    }

    public function getHtml(string $class = 'pagination clearfix', string $active = 'active'): string
    {
        $html = '';
        if ($this->totalPage > 0) {
            $html = str_replace($this->search, [
                $this->totalRow,
                $this->options['header'],
                $this->currentNum,
                $this->totalPage,
                $this->options['unit'],
                $this->getHome(),
                $this->getUp(),
                $this->getPre(),
                $this->getNumLinks($active),
                $this->getNext(),
                $this->getDown(),
                $this->getEnd(),
            ], $this->html);
            if ($this->options['theme'] == 1) {
                $html = '<nav><ul class="' . $class . '">' . str_replace(['[', ']'], ['<li class="page-item">', '</li>'], $html) . '</ul></nav>';
            } else {
                $html = '<div class="' . $class . '">' . str_replace(['[', ']'], ' ', $html) . '</div>';
            }
        }
        return $html;
    }

    private function getHome(): string
    {
        return $this->getLink($this->options['home'], 1);
    }

    protected function getEnd(): string
    {
        if ($this->currentNum < $this->totalPage) {
            return $this->getLink($this->options['end'], $this->totalPage);
        }
        return '';
    }

    protected function getUp(): string
    {
        if ($this->currentNum > 1) {
            return $this->getLink($this->options['up'], $this->currentNum - 1);
        }
        return '';
    }

    protected function getDown(): string
    {
        if ($this->currentNum < $this->totalPage) {
            return $this->getLink($this->options['down'], $this->currentNum + 1);
        }
        return '';
    }

    protected function getPre(): string
    {
        if (ceil($this->currentNum / $this->showNum) > 1) {
            $name = str_replace('n', strval($this->showNum), $this->options['pre']);
            return $this->getLink($name, $this->currentNum - $this->showNum);
        }
        return '';
    }

    protected function getNext(): string
    {
        $allGroup = ceil($this->totalPage / $this->showNum); //总分组数
        $nowGroup = ceil($this->currentNum / $this->showNum); //当前分组数
        if ($nowGroup < $allGroup && $this->currentNum < $this->totalPage) {
            $next = max($this->totalPage, $this->currentNum + $this->showNum);
            $name = str_replace('n', strval($this->showNum), $this->options['next']);
            return $this->getLink($name, $next);
        }
        return '';
    }

    protected function getNumLinks(string $active = 'active'): string
    {
        $start = (int)max(1, min($this->currentNum - ceil($this->showNum / 2), $this->totalPage - $this->showNum));
        $end = (int)min($this->showNum + $start, $this->totalPage);
        $links = '';
        if ($end > 1) {
            for ($i = $start; $i <= $end; $i++) {
                if ($this->currentNum == $i) {
                    if ($this->options['theme'] == 1) {
                        $links .= '<li class="page-item ' . $active . '"><a href="javascript:;" class="page-link">' . $i . '</a></li>';
                    } else {
                        $links .= '[<a href="javascript:;" class="page-link ' . $active . '">' . $i . '</a>]';
                    }
                } else {
                    $links .= $this->getLink($i, $i);
                }
            }
        }
        return $links;
    }

    protected function getLink($name, int $pageNum): string
    {
        if ($pageNum > 0) {
            $url = $this->getUrl($pageNum);
            return '[<a href="' . $url . '" class="page-link">' . $name . '</a>]';
        }
        return '';
    }

    private function getUrl(int $pageNum): string
    {
        $get = $_GET;
        foreach ($this->unsetGetVar as $var) {
            if (isset($get[$var])) unset($get[$var]);
        }
        if ($pageNum > 1) {
            $get[$this->getVar] = $pageNum;
        } elseif (isset($get[$this->getVar])) {
            unset($get[$this->getVar]);
        }
        $get = array_filter($get);
        //ksort($get);
        return Route::init()->buildPageUrl($get);
    }

    private function getCurrentNum(): int
    {
        $currentNum = 1;
        $params = Route::init()->get('params');
        if (isset($params[$this->getVar])) {
            $currentNum = max(1, intval($params[$this->getVar]));
        }
        return ($this->totalPage > 0) ? min($this->totalPage, $currentNum) : $currentNum;
    }
}