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

    private string $key;

    private function __construct()
    {
        $key = get_config('app.key', 'willphp');
        $this->key = $this->getkey($key);
    }

    public function getKey(string $key): string
    {
        return base64_encode(hash('sha256', md5($key), true));
    }

    public function encrypt(string $str, string $key = ''): string
    {
        if (empty($key)) {
            $this->key = $this->getKey($key);
        }

        return base64_encode(openssl_encrypt($str, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, substr($this->key, -16)));
    }

    public function decrypt(string $str, string $key = ''): string
    {
        if (empty($key)) {
            $this->key = $this->getKey($key);
        }
        return openssl_decrypt(base64_decode($str), 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, substr($this->key, -16));
    }
}