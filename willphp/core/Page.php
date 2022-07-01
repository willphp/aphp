<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace willphp\core;
class Page {
	protected static $link = null;
	public static function single()	{
		if (is_null(self::$link)) {
			self::$link = new PageBuilder();
		}
		return self::$link;
	}
	public function __toString() {
		return self::$link->__toString();
	}
	public function __call($method, $params) {
		return call_user_func_array([self::single(), $method], $params);
	}
	public static function __callStatic($name, $arguments) {
		return call_user_func_array([self::single(), $name], $arguments);
	}
}
class PageBuilder {
	protected $totalRow = 0; //总记录数
	protected $pageSize = 10; //每页记录数
	protected $totalPage = 0; //总页数
	protected $pageNum = 5; //显示页码数量
	protected $nowPageNum = 1; //当前页码
	protected $startNum = 0; //limit开始数
	protected $pageVar = 'p'; //分页GET变量
	protected $options = ['home'=>'首页', 'end'=>'尾页', 'up'=>'上一页', 'down'=>'下一页', 'pre'=>'上n页', 'next'=>'下n页', 'header'=>'条记录', 'unit'=>'页', 'theme'=>1];
	protected $search = ['%total%', '%header%', '%nowpage%', '%totalpage%', '%unit%', '%home%', '%up%', '%pre%', '%numlinks%', '%next%', '%down%', '%end%'];
	protected $html = '[%total% %header%] [%nowpage%/%totalpage% %unit%] %home% %up% %pre% %numlinks% %next% %down% %end%';
	/**
	 * 构造
	 */
	public function __construct() {
		$this->pageSize = Config::get('page.page_size', $this->pageSize);
		$this->pageNum = Config::get('page.page_num', $this->pageNum);
		$this->pageVar = Config::get('page.page_var', $this->pageVar);
		$this->html = Config::get('page.page_html', $this->html);
	}
	/**
	 * 分页设置
	 * @param $name 名称
	 * @param $vale 值
	 */
	public function set($name, $value = '') {
		if (is_array($name)) {
			foreach ($name as $k => $v) {
				$this->set($k, $v);
			}
		} else {
			if (isset($this->$name)) {
				$this->$name = ($name == 'html')? $value : strip_tags($value);
			} elseif (array_key_exists($name, $this->options)) {
				$this->options[$name] = strip_tags($value);
			}
		}
		return $this;
	}
	/**
	 * 制作页码
	 * @param $total 总记录数
	 * @return string
	 */
	public function make($total) {
		$this->totalRow = intval($total);
		$this->totalPage = ceil($this->totalRow / $this->pageSize);
		$this->nowPageNum = $this->getNowPageNum();
		$this->startNum = $this->pageSize * ($this->nowPageNum - 1); 
		return $this;
	}
	/**
	 * 获取属性
	 * @param string $type
	 * @return number|string
	 */
	public function getAttr($attr = '') {
		$tmp = [];
		$tmp['count'] = $this->totalRow; //总记录数
		$tmp['offset'] = $this->startNum; //开始数
		$tmp['limit'] = $this->pageSize; //每页记录数
		$tmp['page'] = $this->nowPageNum; //当前第几页
		$tmp['page_count'] = $this->totalPage; //总页数
		if (empty($attr)) {
			return $tmp;
		}
		return isset($tmp[$attr])? $tmp[$attr] : 0;
	}	
	/**
	 * 输出对象
	 * @return string
	 */
	public function __toString() {
		return $this->getHtml();
	}
	/**
	 * 获取当前页码
	 * @return string
	 */
	protected function getNowPageNum() {
		$nowPage = isset($_GET[$this->pageVar])? max(1, intval($_GET[$this->pageVar])) : 1;
		return ($this->totalPage > 0)? min($this->totalPage, $nowPage) : $nowPage;
	}
	/**
	 * 获取limit
	 * @return string
	 */
	public function getLimit() {
		return $this->startNum.','.$this->pageSize;
	}
	/**
	 * 获取分页html
	 * @return string
	 */
	public function getHtml($class = 'pagination', $active = 'active') {
		if ($this->totalPage > 0) {
			$html = str_replace(
					$this->search,
					[
							$this->totalRow,
							$this->options['header'],
							$this->nowPageNum,
							$this->totalPage,
							$this->options['unit'],
							$this->getHome(),
							$this->getUp(),
							$this->getPre(),
							$this->getNumLinks($active),
							$this->getNext(),
							$this->getDown(),
							$this->getEnd(),
					],
					$this->html
					);
			if ($this->options['theme'] == 1) {
				$html = str_replace(['[', ']'], ' ', $html);
				return '<div class="'.$class.' cl">'.$html.'</div>';
			} elseif ($this->options['theme'] == 2) {
				$html = str_replace(['[', ']'], ['<li>', '</li>'], $html);
				return '<nav><ul class="'.$class.' cl">'.$html.'</ul></nav>';
			}
			return '<div class="'.$class.' cl">'.$html.'</div>';
		}
		return '';
	}
	/**
	 * 获取首页
	 * @return string
	 */
	protected function getHome() {
		return $this->getLink($this->options['home'], 1);
	}
	/**
	 * 获取尾页
	 * @return string
	 */
	protected function getEnd() {
		if ($this->nowPageNum < $this->totalPage) {
			return $this->getLink($this->options['end'], $this->totalPage);
		}
		return '';
	}
	/**
	 * 获取上一页
	 * @return string
	 */
	protected function getUp() {
		if ($this->nowPageNum > 1) {
			return $this->getLink($this->options['up'], $this->nowPageNum - 1);
		}
		return '';
	}
	/**
	 * 获取下一页
	 * @return string
	 */
	protected function getDown() {
		if ($this->nowPageNum < $this->totalPage) {
			return $this->getLink($this->options['down'], $this->nowPageNum + 1);
		}
		return '';
	}
	/**
	 * 获取上n页
	 * @return string
	 */
	protected function getPre() {
		if (ceil($this->nowPageNum / $this->pageNum) > 1) {
			$name = str_replace('n', $this->pageNum, $this->options['pre']);
			return $this->getLink($name, $this->nowPageNum - $this->pageNum);
		}
		return '';
	}
	/**
	 * 获取下n页
	 * @return string
	 */
	protected function getNext() {
		$allGroup = ceil($this->totalPage / $this->pageNum); //总分组数
		$nowGroup = ceil($this->nowPageNum / $this->pageNum); //当前分组数
		if ($nowGroup < $allGroup && $this->nowPageNum < $this->totalPage) {
			$next = max($this->totalPage, $this->nowPageNum + $this->pageNum);
			$name = str_replace('n', $this->pageNum, $this->options['next']);
			return $this->getLink($name, $next);
		}
		return '';
	}
	/**
	 * 获取数字分页
	 * @return string
	 */
	protected function getNumLinks($active = 'active') {
		$start = max(1, min($this->nowPageNum - ceil($this->pageNum / 2), $this->totalPage - $this->pageNum));
		$end = min($this->pageNum + $start, $this->totalPage);
		$links = '';
		if ($end > 1) {
			for ($i = $start; $i <= $end; $i++) {
				if ($this->nowPageNum == $i) {
					if ($this->options['theme'] == 2) {
						$links .= '<li class="'.$active.'"><a href="javascript:;">'.$i.'</a></li>';
					} else {
						$links .= '[<a href="javascript:;" class="'.$active.'">'.$i.'</a>]';
					}
				} else {
					$links .= $this->getLink($i, $i);
				}
			}
		}
		return $links;
	}
	/**
	 * 获取链接
	 * @param string $name 链接标题
	 * @param int $pageNum 页码
	 * @return string
	 */
	protected function getLink($name, $pageNum) {
		if ($pageNum > 0) {
			$url = $this->getUrl($pageNum);
			return '[<a href="'.$url.'">'.$name.'</a>]';
		}
		return '';
	}
	/**
	 * 获取页面url(包含&p=页码)
	 * @param int $pageNum 页码
	 * @return string
	 */
	protected function getUrl($pageNum) {	
		$parse_url = Config::get('page.parse_url', ''); //获取url处理方法
		$isParse = is_callable($parse_url); 		
		$get = $_GET;
		if (isset($get['csrf_token'])) unset($get['csrf_token']);		
		$pn = $isParse? 1 : 0;		
		if ($pageNum > $pn) {
			$get[$this->pageVar] = $pageNum;
		} elseif (isset($get[$this->pageVar])) {
			unset($get[$this->pageVar]);
		}
		$get = array_filter($get);
		ksort($get);		
		$param = http_build_query($get);	
		if ($isParse) {			
			$url = call_user_func_array($parse_url, [$param]);
		} elseif ($_SERVER['QUERY_STRING']) {
			$url = str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']).$param;
		} else {
			$url = empty($param)? $_SERVER['REQUEST_URI'] : $_SERVER['REQUEST_URI'].'?'.$param;
		}
		return $url;
	}
}