<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace extend\upload;
defined('APHP_TOP') or die('Access Denied');

use aphp\core\Tool;
use Exception;
use extend\thumb\Thumb;
use aphp\core\Config;
use aphp\core\Single;

class Upload
{
    use Single;

    protected string $config_file;
    protected array $config;
    protected string $path;
    protected string $error = '';

    private function __construct(string $type = 'img')
    {
        $this->config_file = APHP_TOP . '/config/upload.php';
        $this->config = Config::init()->get('upload.' . $type, []);
        if (empty($this->config)) {
            $this->install();
            throw new Exception('已加载默认配置，请重试');
        }
        $this->path = Tool::dir_init(APHP_TOP . '/' . $this->config['path']);
    }

    public function install(): bool
    {
        $source_file = APHP_TOP . '/extend/upload/config/upload.php';
        if (copy($source_file, $this->config_file)) {
            Config::init()->refresh();
            return true;
        }
        return false;
    }

    public function unload(): bool
    {
        return !is_file($this->config_file) or unlink($this->config_file);
    }

    public function saveBase64(string $base64, string $fileName = ''): array
    {
        [$mime, $data] = explode(',', $base64);
        $ext = $this->getExt($mime);
        if (!$ext) {
            $this->error = '文件类型不允许';
            return [];
        }
        if (empty($fileName)) {
            $fileName = mt_rand(1, 9999) . time();
        }
        $filePath = $this->path . '/' . $fileName . $ext;
        $ok = file_put_contents($filePath, base64_decode($data), LOCK_EX);
        if (!$ok) {
            $this->error = '文件保存失败';
            return [];
        }
        $filePath = $this->image_rewrite($filePath, $ext, $filePath);
        if ($this->config['auto_thumb']) {
            $filePath = $this->thumb($filePath);
        }
        $file = [];
        $file['path'] = substr($filePath, strlen(APHP_TOP . '/public')); //新文件名
        $file['url'] = __HOST__ . $file['path'];
        $file['uptime'] = time();
        return $file;
    }

    protected function getExt(string $type): string
    {
        $types = ['.jpg' => 'image/jpeg', '.png' => 'image/png', '.gif' => 'image/gif'];
        foreach ($types as $ext => $mime) {
            if (str_contains($type, $mime)) {
                return $ext;
            }
        }
        return '';
    }

    private function thumb(string $imgFile): string
    {
        $thumbType = $this->config['thumb']['thumb_type'] ?? 6;
        $maxWidth = $this->config['thumb']['max_width'] ?? 0;
        $width = $this->config['thumb']['width'] ?? 0;
        $height = $this->config['thumb']['height'] ?? 0;
        $delSrc = $this->config['thumb']['del_src'] ?? false;
        $isThumb = false;
        $imgInfo = getimagesize($imgFile);
        if ($maxWidth > 0 && $imgInfo[0] > $maxWidth) {
            $width = $maxWidth;
            $thumbType = 1;
            $isThumb = true;
        } elseif ($width > 0 || $height > 0) {
            $isThumb = true;
        }
        if ($isThumb) {
            $thumbFile = $this->path . '/w' . $width . 'h' . $height . '_' . basename($imgFile);
            $okThumb = Thumb::init()->make($imgFile, $thumbFile, $width, $height, $thumbType);
            if ($okThumb) {
                if ($delSrc) {
                    unlink($imgFile);
                }
                return $thumbFile;
            }
        }
        return $imgFile;
    }

    public function save(): array
    {
        $files = $this->getInputFile();
        if (empty($files)) {
            $this->error = '没有任何文件上传';
            return [];
        }
        $uploaded = [];
        foreach ($files as $file) {
            $info = pathinfo($file['name']);
            $file['ext'] = $info['extension'] ?? '';
            $file['filetype'] = $this->getFileType($file['ext']);
            if (!$this->checkFile($file)) {
                continue;
            }
            $upload = $this->move($file);
            if ($upload) {
                $uploaded[] = $upload;
            }
        }
        return $uploaded;
    }

    private function getInputFile(): array
    {
        if (empty($_FILES)) {
            return [];
        }
        $info = [];
        $n = 0;
        foreach ($_FILES as $name => $v) {
            if (is_array($v['name'])) {
                $count = count($v['name']);
                for ($i = 0; $i < $count; $i++) {
                    foreach ($v as $m => $k) {
                        $info [$n][$m] = $k[$i];
                    }
                    $info[$n]['field_name'] = $name;
                    $n++;
                }
            } else {
                $info[$n] = $v;
                $info[$n]['field_name'] = $name;
                $n++;
            }
        }
        return array_filter($info, fn($v) => $v['error'] == 0);
    }

    protected function getFileType(string $ext): int
    {
        if (empty($ext)) {
            return 0;
        }
        if (in_array($ext, ['jpg', 'jpeg', 'gif', 'png'])) {
            return 1;
        }
        if (in_array($ext, ['zip', 'rar', '7z'])) {
            return 2;
        }
        if (in_array($ext, ['doc', 'txt'])) {
            return 3;
        }
        return 0;
    }

    private function checkFile(array $file): bool
    {
        if ($file['error'] != 0) {
            $this->setError($file['error']);
            return false;
        }
        if (!in_array(strtolower($file['ext']), $this->config['allow_ext'])) {
            $this->error = '文件类型不允许';
            return false;
        }
        if (strstr(strtolower($file['type']), 'image') && !getimagesize($file['tmp_name'])) {
            $this->error = '上传内容不是一个合法图片';
            return false;
        }
        if ($file['size'] > $this->config['allow_size']) {
            $this->error = '上传文件不能大于 ' . Tool::size2kb($this->config['allow_size']);
            return false;
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->error = '非法文件';
            return false;
        }
        return true;
    }

    private function setError(int $code): void
    {
        $errors = [];
        $errors[UPLOAD_ERR_INI_SIZE] = '上传文件超过PHP.INI配置文件允许的大小';
        $errors[UPLOAD_ERR_FORM_SIZE] = '文件超过表单限制大小';
        $errors[UPLOAD_ERR_PARTIAL] = '文件只上有部分上传';
        $errors[UPLOAD_ERR_NO_FILE] = '没有上传文件';
        $errors[UPLOAD_ERR_NO_TMP_DIR] = '没有上传临时文件夹';
        $errors[UPLOAD_ERR_CANT_WRITE] = '写入临时文件夹出错';
        $this->error = $errors[$code] ?? '未知错误';
    }

    private function move(array $file)
    {
        $fileName = mt_rand(1, 9999) . time() . '.' . $file['ext'];
        $filePath = $this->path . '/' . $fileName; //新文件 旧文件$file['tmp_name']
        //图片处理
        if ($file['filetype'] == 1) {
            $filePath = $this->image_rewrite($file['tmp_name'], $file['ext'], $filePath);
            if ($this->config['auto_thumb']) {
                $filePath = $this->thumb($filePath);
            }
        } elseif (!move_uploaded_file($file['tmp_name'], $filePath) && is_file($filePath)) {
            $this->error = '移动临时文件失败';
            return false;
        }
        $file['path'] = substr($filePath, strlen(APHP_TOP . '/public')); //新文件名
        //$file['url'] = __HOST__ . $file['path'];
        $file['size'] = filesize($filePath);
        $file['uptime'] = time();
        unset($file['tmp_name']);
        return $file;
    }

    protected function image_rewrite(string $src, string $ext, string $rewrite): string
    {
        $ext = trim($ext, '.');
        if ($ext == 'png') {
            $img = imagecreatefrompng($src);
            imagesavealpha($img, true);
            imagepng($img, $rewrite, 9);
        } elseif ($ext == 'gif') {
            $img = imagecreatefromgif($src);
            imagegif($img, $rewrite);
        } else {
            $img = imagecreatefromjpeg($src);
            imagejpeg($img, $rewrite, 100);
        }
        imagedestroy($img);
        return $rewrite;
    }

    public function getError(): string
    {
        return $this->error;
    }
}