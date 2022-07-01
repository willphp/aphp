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
class Crypt {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new CryptBuilder();
		}
		return self::$link;
	}
	public function __call($method, $params) {
		return call_user_func_array([self::single(), $method], $params);
	}
	public static function __callStatic($name, $arguments) {
		return call_user_func_array([self::single(), $name], $arguments);
	}
}
class CryptBuilder {
	protected $secureKey = 'd0456cc4eb4eec479dba0ceefa6488c143a03299a3c3fed3d8ce7b820f3aca81';
	/**
	 * 设置密钥
	 * @param string $key
	 * @return string
	 */
	public function key($key = '') {
		$key = $key ? $key : Config::get('app.key', $this->secureKey);
		$this->secureKey = md5($key);
		return base64_decode(hash('sha256', $this->secureKey, true));
	}
	/**
	 * 加密
	 * @param string $input     加密字符
	 * @param string $secureKey 加密key
	 * @return string
	 */
	public function encrypt($input, $secureKey = '') {
		$encrypt = openssl_encrypt($input, 'aes-256-cbc', $this->key($secureKey), OPENSSL_RAW_DATA, substr($this->secureKey, -16));
		return base64_encode($encrypt);
	}
	/**
	 * 解密
	 * @param string $input     解密字符
	 * @param string $secureKey 加密key
	 * @return string
	 */
	public function decrypt($input, $secureKey = '') {
		$encrypted = base64_decode($input);
		$decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $this->key($secureKey), OPENSSL_RAW_DATA, substr($this->secureKey, -16));
		return $decrypted;
	}
}