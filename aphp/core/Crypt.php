<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
class Crypt
{
    use Single;

    protected string $salt;

    private function __construct()
    {
        $appKey = Config::init()->get('app.app_key', 'b64f03169423386de0b080a248ca3526');
        $this->salt = $this->makeSalt($appKey);
    }

    protected function makeSalt(string $salt): string
    {
        return base64_encode(hash('sha256', md5($salt), true));
    }

    public function encrypt(string $string, string $salt = ''): string
    {
        if (!empty($salt)) {
            $this->salt = $this->makeSalt($salt);
        }
        return base64_encode(openssl_encrypt($string, 'aes-256-cbc', $this->salt, OPENSSL_RAW_DATA, substr($this->salt, -16)));
    }

    public function decrypt(string $string, string $salt = ''): string
    {
        if (!empty($salt)) {
            $this->salt = $this->makeSalt($salt);
        }
        return openssl_decrypt(base64_decode($string), 'aes-256-cbc', $this->salt, OPENSSL_RAW_DATA, substr($this->salt, -16));
    }
}