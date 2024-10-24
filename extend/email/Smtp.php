<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace extend\email;

use aphp\core\Single;
use Exception;
use finfo;

/**
 * Email SMTP类
 */
class Smtp
{
    use Single;

    private string $host; // SMTP服务器
    private int $port; // 端口 默认:25 ssl:465
    private string $user; // 账户
    private string $pass; // 授权码
    private int $is_ssl; // SSL验证
    private int $is_open; // 是否开启
    private $socket; // socket
    private string $mime_boundary = '----=_NextPart_5ADB22FC_0AC35138_76A8687D'; // 邮件边界符
    public string $error = ''; // 错误信息

    private function __construct(array $config = [])
    {
        if (empty($config)) {
            $config = config_get('email', []);
        }
        $config['smtp_host'] ??= 'smtp.163.com';
        $config['smtp_port'] ??= 25;
        $config['smtp_user'] ??= '';
        $config['smtp_pass'] ??= '';
        $config['smtp_ssl'] ??= 0;
        $config['send_open'] ??= 0;
        $this->host = $config['smtp_host'];
        $this->port = (int)$config['smtp_port'];
        $this->user = base64_encode($config['smtp_user']);
        $this->pass = base64_encode($config['smtp_pass']);
        $this->is_ssl = (int)$config['smtp_ssl'];
        $this->is_open = (int)$config['send_open'];
        if ($this->is_ssl > 0) {
            $this->host = 'ssl://' . $this->host;
        }
    }

    // 发送邮件
    public function send(string $to, string $subject, string $body, array $attach = []): bool
    {
        if ($this->is_open == 0) {
            $this->error = '邮件发送已关闭';
            return false;
        }
        if (empty($this->user) || empty($this->pass)) {
            $this->error = '未设置SMTP账户或SMTP授权码';
            return false;
        }
        try {
            $this->socketConnect('220');
            $this->socketWrite('HELO JunQiu' . PHP_EOL, '250');
            $this->socketWrite('AUTH LOGIN' . PHP_EOL, '334');
            $this->socketWrite($this->user . PHP_EOL, '334');
            $this->socketWrite($this->pass . PHP_EOL, '235');
            $this->socketWrite("MAIL FROM:<" . base64_decode($this->user) . ">" . PHP_EOL, '250');
            $this->socketWrite("RCPT TO:<" . $to . ">" . PHP_EOL, '250');
            $this->socketWrite("DATA" . PHP_EOL, '354');
            // 构建邮件
            $mail = 'From:<' . base64_decode($this->user) . '>' . PHP_EOL;
            $mail .= 'To:<' . $to . '>' . PHP_EOL;
            $mail .= 'Subject:' . $subject . PHP_EOL;
            $mail .= !empty($attach) ? 'Content-Type: multipart/mixed;' . PHP_EOL : 'Content-Type: multipart/related;' . PHP_EOL;
            $mail .= "    boundary=\"$this->mime_boundary\"" . PHP_EOL;
            $mail .= 'MIME-Version: 1.0' . PHP_EOL;
            $mail .= 'Content-Transfer-Encoding: 8Bit' . PHP_EOL;
            $mail .= PHP_EOL . 'This is a multi-part message in MIME format.' . PHP_EOL;
            $mail .= PHP_EOL . '--' . $this->mime_boundary . PHP_EOL;
            $mail .= 'Content-Type: text/html;' . PHP_EOL . '    charset="utf-8"' . PHP_EOL . 'Content-Transfer-Encoding: base64' . PHP_EOL;
            $mail .= PHP_EOL . base64_encode($body) . PHP_EOL;
            if (!empty($attach)) {
                foreach ($attach as $v) {
                    $mail .= $this->creatMailAttach($v);
                }
            }
            $mail .= PHP_EOL . "--$this->mime_boundary--";
            $this->socketWrite($mail . PHP_EOL . '.' . PHP_EOL, '250');
            $this->socketWrite("QUIT" . PHP_EOL, '221');
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    // 获取错误信息
    public function getError(): string
    {
        return $this->error;
    }

    // 构建邮件附件
    protected function creatMailAttach(string $attachUrl): string
    {
        if (!file_exists($attachUrl)) {
            throw new Exception("文件错误 - $attachUrl 不存在");
        }
        $type = 'application/octet-stream;';
        $charset = 'utf-8';
        if (class_exists(finfo::class)) {
            $f_info = new finfo(FILEINFO_MIME);
            $file_info = $f_info->file($attachUrl);
            [$type, $charset] = explode(' ', $file_info);
        }
        $base64Data = base64_encode(file_get_contents($attachUrl));
        $attach = PHP_EOL . '--' . $this->mime_boundary . PHP_EOL;
        $attach .= 'Content-Type: ' . $type . PHP_EOL . "    charset=\"" . $charset . "\";" . PHP_EOL . "    name=\"" . basename($attachUrl) . "\"" . PHP_EOL . "Content-Disposition: attachment; filename=\"" . basename($attachUrl) . "\"" . PHP_EOL . "Content-Transfer-Encoding: base64" . PHP_EOL;
        $attach .= PHP_EOL . $base64Data . PHP_EOL;
        return $attach;
    }

    // 建立连接
    protected function socketConnect(string $code): void
    {
        $this->socket = fsockopen($this->host, $this->port);
        $codeResult = $this->socketRead();
        if ($code != $codeResult[0]) {
            throw new Exception($codeResult[1]);
        }
    }

    // 从流中读数据
    protected function socketRead(): array
    {
        $result = fgets($this->socket);
        if (!$result) {
            throw new Exception('Read Error - 读取数据失败');
        }
        preg_match('/\d{3}/', $result, $code);
        $code[1] = $result;
        return $code;
    }

    // 向流中写入数据
    protected function socketWrite(string $message, string $code): void
    {
        $result = fwrite($this->socket, $message);
        if (!$result) {
            throw new Exception('Write Error - 写入数据失败');
        }
        $codeResult = $this->socketRead();
        if ($code != $codeResult[0]) {
            throw new Exception($codeResult[1]);
        }
    }
}