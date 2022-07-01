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
class Collection {
	protected $link;
	public function __call( $method, $params ) {
		if (is_null($this->link ) ) {
			$this->link = new CollectionBuilder();	
		}		
		return call_user_func_array([$this->link, $method], $params);
	}
	public static function __callStatic($name, $arguments) {
		return call_user_func_array([new static(), $name], $arguments);
	}
}
class CollectionBuilder implements \Iterator, \ArrayAccess {	
	protected $items = [];
	public function current() {
		return current($this->items);
	}
	public function next() {
		next($this->items);
	}
	public function key() {
		return key($this->items);
	}
	public function valid()	{
		return current($this->items);
	}
	public function rewind() {
		reset($this->items);
	}
	public function offsetExists($offset) {
		return isset($this->items[$offset]);
	}
	public function offsetGet($key)	{
		return isset($this->items[$key]) ? $this->items[$key] : null;
	}
	public function offsetSet($offset, $value) {
		$this->items[$offset] = $value;
	}
	public function offsetUnset($key) {
		if (isset($this->items[$key])) {
			unset($this->items[$key]);
		}
	}
	public function toArray() {
		$res = [];
		foreach ($this->items as $k => $v) {
			if (is_object($v) && method_exists($v, 'toArray')) {
				$res[] = $v->toArray();
			} else {
				$res[] = $v;
			}
		}
		return $res;
	}
	public function make($data) {
		$this->items = $data;
		return $this;
	}	
	public function links()	{
		return Page::single();
	}
	public function getAttr($attr = '') {
		return Page::getAttr($attr);
	}
}