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
 * 加密解密类
 */
class Crypt
{
    use Single;

    protected string $salt; //加密盐

    //初始化加密盐
    private function __construct()
    {
        $this->salt = $this->makeSalt(Config::init()->get('app.app_key', 'willphp'));
    }

    //生成加密盐
    protected function makeSalt(string $salt): string
    {
        return base64_encode(hash('sha256', md5($salt), true));
    }

    //加密
    public function encrypt(string $string, string $salt = ''): string
    {
        if (!empty($salt)) {
            $this->salt = $this->makeSalt($salt);
        }
        return base64_encode(openssl_encrypt($string, 'aes-256-cbc', $this->salt, OPENSSL_RAW_DATA, substr($this->salt, -16)));
    }

    //解密
    public function decrypt(string $string, string $salt = ''): string
    {
        if (!empty($salt)) {
            $this->salt = $this->makeSalt($salt);
        }
        return openssl_decrypt(base64_decode($string), 'aes-256-cbc', $this->salt, OPENSSL_RAW_DATA, substr($this->salt, -16));
    }
}