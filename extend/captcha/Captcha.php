<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace extend\captcha;
class Captcha
{
    private $img;
    private string $code;
    private int $width = 100;
    private int $height = 30;
    private string $bgColor = '#ffffff';
    private string $codeStr = '23456789abcdefghjkmnpqrstuvwsyz';
    private int $num = 4;
    private string $font;
    private int $fontSize = 16;
    private string $fontColor = '';

    public function __construct()
    {
        $this->font = __DIR__ . '/font.ttf';
    }

    public function __call($name, $arguments)
    {
        $this->$name = current($arguments);
        return $this;
    }

    public function make()
    {
        $this->create();
        if (PHP_SAPI != 'cli') {
            header("Content-type:image/png");
            imagepng($this->img);
            imagedestroy($this->img);
            exit;
        }
        return true;
    }

    public function getBase64(): string
    {
        $this->create();
        ob_start();
        imagepng($this->img);
        $buffer = ob_get_clean();
        ob_end_clean();
        return base64_encode($buffer);
    }

    public function get(): string
    {
        return session_flash('captcha');
    }

    private function createCode(): void
    {
        $code = '';
        for ($i = 0; $i < $this->num; $i++) {
            $code .= $this->codeStr [mt_rand(0, strlen($this->codeStr) - 1)];
        }
        $this->code = strtoupper($code);
        session_flash('captcha', $this->code);
    }

    private function create(): void
    {
        if (!$this->checkGD()) {
            return;
        }
        $this->createCode();
        $w = $this->width;
        $h = $this->height;
        $bgColor = $this->bgColor;
        $img = imagecreatetruecolor($w, $h);
        $bgColor = imagecolorallocate(
            $img,
            hexdec(substr($bgColor, 1, 2)),
            hexdec(substr($bgColor, 3, 2)),
            hexdec(substr($bgColor, 5, 2))
        );
        imagefill($img, 0, 0, $bgColor);
        $this->img = $img;
        $this->createLine();
        $fontColor = $this->createFont();
        $this->createPix($fontColor);
    }

    private function createLine(): void
    {
        $w = $this->width;
        $h = $this->height;
        $line_color = "#dcdcdc";
        $color = imagecolorallocate(
            $this->img,
            hexdec(substr($line_color, 1, 2)),
            hexdec(substr($line_color, 3, 2)),
            hexdec(substr($line_color, 5, 2))
        );
        $l = $h / 5;
        for ($i = 1; $i < $l; $i++) {
            $step = $i * 5;
            imageline($this->img, 0, $step, $w, $step, $color);
        }
        $l = $w / 10;
        for ($i = 1; $i < $l; $i++) {
            $step = $i * 10;
            imageline($this->img, $step, 0, $step, $h, $color);
        }
    }

    private function createFont(): int
    {
        $fontColor = '';
        $color = $this->fontColor;
        if (!empty($color)) {
            $fontColor = imagecolorallocate(
                $this->img,
                hexdec(substr($color, 1, 2)),
                hexdec(substr($color, 3, 2)),
                hexdec(substr($color, 5, 2))
            );
        }
        $x = ($this->width - 10) / $this->num;
        for ($i = 0; $i < $this->num; $i++) {
            if (empty($color)) {
                $fontColor = imagecolorallocate(
                    $this->img,
                    mt_rand(50, 155),
                    mt_rand(50, 155),
                    mt_rand(50, 155)
                );
            }
            imagettftext(
                $this->img,
                $this->fontSize,
                mt_rand(-30, 30),
                intval($x * $i + mt_rand(6, 10)),
                mt_rand(intval($this->height / 1.3), $this->height - 5),
                $fontColor,
                $this->font,
                $this->code[$i]
            );
        }
        return (int)$fontColor;
    }

    private function createPix(int $pix_color): void
    {
        for ($i = 0; $i < 50; $i++) {
            imagesetpixel(
                $this->img,
                mt_rand(0, $this->width),
                mt_rand(0, $this->height),
                $pix_color
            );
        }
        for ($i = 0; $i < 2; $i++) {
            imageline(
                $this->img,
                mt_rand(0, $this->width),
                mt_rand(0, $this->height),
                mt_rand(0, $this->width),
                mt_rand(0, $this->height),
                $pix_color
            );
        }
        for ($i = 0; $i < 1; $i++) {
            imagearc(
                $this->img,
                mt_rand(0, $this->width),
                mt_rand(0, $this->height),
                mt_rand(0, $this->width),
                mt_rand(0, $this->height),
                mt_rand(0, 160),
                mt_rand(0, 200),
                $pix_color
            );
        }
        imagesetthickness($this->img, 1);
    }

    private function checkGD(): bool
    {
        return extension_loaded('gd') && function_exists("imagepng");
    }
}