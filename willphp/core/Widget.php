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
/**
 * 部件基类
 */
abstract class Widget {
	protected $table; //表名标识
	protected $expire = 0; //秒 有效时间 0.永久	
	/**
	 * 设置缓存
	 * @param string $id ID
	 * @param array $options 选项
	 * @return mixed
	 */
	abstract public function set($id = '', $options = []);
	/**
	 * 获取缓存
	 * @param string $id ID
	 * @param array $options 选项
	 * @return mixed
	 */
	public function get($id = '', $options = []) {
		$name = $this->getName($id, $options);
		$data = Cache::get($name);
		if (!$data) {
			$data = $this->set($id, $options);
			if ($data) {
				Cache::set($name, $data, $this->expire);
			}
		}
		return $data;
	}
	/**
	 * 更新缓存
	 * @return bool
	 */
	public function update() {
		return Cache::flush($this->getType());
	}
	/**
	 * 获取缓存标识
	 * @param string $id ID
	 * @param array $options 选项
	 * @return string
	 */
	protected function getName($id = '', $options = []) {
		$name = basename(str_replace('\\', '/', get_class($this))).$id;
		if (!empty($options)) {
			ksort($options);
			$name .= http_build_query($options);
		}
		return $this->getType().'.'.md5($name);
	}
	/**
	 * 获取类型
	 * @return string
	 */
	protected function getType() {
		return ($this->table)? 'widget/'.$this->table : 'widget';	
	}
}