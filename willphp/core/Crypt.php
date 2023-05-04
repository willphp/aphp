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
class Crypt
{
    use Single;

    protected string $salt;

    private function __construct()
    {
        $this->salt = $this->getSalt(Config::init()->get('app.app_key', 'willphp'));
    }

    public function encrypt(string $string, string $salt = ''): string
    {
        if (!empty($salt)) {
            $this->salt = $this->getSalt($salt);
        }
        return base64_encode(openssl_encrypt($string, 'aes-256-cbc', $this->salt, OPENSSL_RAW_DATA, substr($this->salt, -16)));
    }

    public function decrypt(string $string, string $salt = ''): string
    {
        if (!empty($salt)) {
            $this->salt = $this->getSalt($salt);
        }
        return openssl_decrypt(base64_decode($string), 'aes-256-cbc', $this->salt, OPENSSL_RAW_DATA, substr($this->salt, -16));
    }

    protected function getSalt(string $salt): string
    {
        return base64_encode(hash('sha256', md5($salt), true));
    }
}