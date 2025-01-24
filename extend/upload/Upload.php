<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace extend\upload;
defined('ROOT_PATH') or die('Access Denied');

use aphp\core\Tool;
use extend\thumb\Thumb;
use aphp\core\Config;
use aphp\core\Single;

/**
 * 上传处理类
 */
class Upload
{
    use Single;

    protected array $config; // 上传配置
    protected array $type = ['image' => 'jpg|jpeg|gif|png']; // 文件类型
    protected array $imageExt = ['jpg', 'jpeg', 'gif', 'png']; // 图片后缀
    protected string $dir; // 存储路径
    protected string $error = ''; // 错误信息

    private function __construct(string $api = 'image')
    {
        $config = Config::init()->get('upload', []);
        $this->type = $config['file_type'] ?? $this->type; // 文件类型
        if (isset($this->type['image'])) {
            $this->imageExt = explode('|', $this->type['image']); // 图片后缀
        }
        $api = isset($config['api'][$api]) ? $api : 'image';
        $this->config = $config['api'][$api] ?? []; // 上传配置
        $this->config['api_type'] = $api; // 上传api类型
        $this->config['allow_type'] ??= 'image'; // 允许类型
        $this->config['allow_ext'] = $this->_allow_ext($this->config['allow_type']); // 允许后缀
        $this->config['allow_size'] ??= 2097152; // 允许大小2M
        $this->config['image_auto_cut'] ??= false; // 图片自动裁剪
        $this->dir = ROOT_PATH . '/public/uploads'; // 存储目录
    }

    // 根据文件类型获取文件后缀
    protected function _allow_ext(string $allow_type): array
    {
        if ($allow_type == '*') {
            $allow_ext = implode('|', $this->type);
        } else {
            $allow_ext = $this->type[$allow_type] ?? $this->type['image'] ?? '';
        }
        return explode('|', $allow_ext);
    }

    // 保存文件
    public function save(): array
    {
        $input = $this->_input(); // 获取上传
        if (empty($input)) {
            $this->error = '没有文件被上传';
            return [];
        }
        $upload = [];
        foreach ($input as $file) {
            $file['ext'] = pathinfo($file['name'], PATHINFO_EXTENSION); // 文件后缀
            // 检查文件
            if (!$this->_check($file)) {
                continue;
            }
            $move = $this->_move($file); // 移动文件
            if (!empty($move)) {
                $upload[] = $move;
            }
        }
        return $upload;
    }

    // 保存base64图片文件
    public function saveBase64Image(string $base64, string $save_name = ''): array
    {
        [$mime, $data] = explode(',', $base64);
        $mime = strtolower($mime);
        $ext = $this->_ext($mime);
        if ($ext == '') {
            $this->error = '文件类型不允许';
            return [];
        }
        if (empty($save_name)) {
            $save_name = mt_rand(1000, 9999) . time();
        }
        $save_to = Tool::dir_init($this->dir . '/image') . '/' . $save_name . '.' . $ext;
        $save = file_put_contents($save_to, base64_decode($data), LOCK_EX);
        if (!$save) {
            $this->error = '文件上传失败';
            return [];
        }
        $save_to = $this->_image_rewrite($save_to, $ext, $save_to);
        if ($this->config['image_auto_cut']) {
            $save_to = $this->_image_auto_cut($save_to); // 图片自动裁剪
        }
        $file = [];
        $file['name'] = $save_name;
        $file['path'] = substr($save_to, strlen(ROOT_PATH . '/public'));
        $file['size'] = filesize($save_to);
        $file['type'] = $mime;
        $file['ext'] = $ext;
        $file['file_type'] = 'image';
        $file['api_type'] = $this->config['api_type'];
        $file['upload_time'] = time();
        return $file;
    }

    // 获取文件后缀
    protected function _ext(string $mime): string
    {
        $mime_map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        foreach ($mime_map as $type => $ext) {
            if (str_contains($mime, $type)) {
                return $ext;
            }
        }
        return '';
    }

    // 获取上传
    protected function _input(): array
    {
        if (empty($_FILES)) {
            return [];
        }
        $data = [];
        $i = 0;
        foreach ($_FILES as $field => $file) {
            if (!is_array($file['name'])) {
                $data[$i] = $file;
                $data[$i]['field'] = $field;
                $i++;
            } else {
                $count = count($file['name']);
                for ($n = 0; $n < $count; $n++) {
                    foreach ($file as $k => $v) {
                        $data[$i][$k] = $v[$n];
                    }
                    $data[$i]['field'] = $field;
                    $i++;
                }
            }
        }
        return $data;
    }

    // 检查文件
    protected function _check(array $file): bool
    {
        if ($file['error'] != 0) {
            $this->error = $this->_code_msg($file['error']);
            return false;
        }
        if (!in_array(strtolower($file['ext']), $this->config['allow_ext'])) {
            $this->error = '文件类型不允许';
            return false;
        }
        if ($file['size'] > $this->config['allow_size']) {
            $this->error = '文件大小不能超过 ' . Tool::size2kb($this->config['allow_size']);
            return false;
        }
        if (in_array($file['ext'], $this->imageExt) && !getimagesize($file['tmp_name'])) {
            $this->error = '文件不是有效的图片';
            return false;
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->error = '文件不是有效的上传文件';
            return false;
        }
        return true;
    }

    // 根据错误代码获取信息
    protected function _code_msg(int $code = 0): string
    {
        $msg = [
            1 => '上传大小超过PHP.INI配置限制',
            2 => '上传大小超过表单MAX_FILE_SIZE配置',
            3 => '文件只有部分被上传',
            4 => '没有文件被上传',
            6 => '找不到临时文件夹',
            7 => '文件写入失败',
        ];
        return $msg[$code] ?? '未知错误';
    }

    // 移动文件
    protected function _move(array $file): array
    {
        $file_type = $this->_file_type($this->config['allow_type'], $file['ext']); // 文件类型
        $file_name = mt_rand(1000, 9999) . time() . '.' . $file['ext']; // 文件名
        $path = $this->config['path'] ?? $file_type; // 文件路径
        $save_to = $real_save_to = Tool::dir_init($this->dir.'/'.$path) . '/' . $file_name;
        if (isset($this->config['real_path'])) {
            $real_save_to = Tool::dir_init(ROOT_PATH.'/download/'.$this->config['real_path']) . '/' . $file_name;
        }
        if (in_array($file['ext'], $this->imageExt)) {
            $save_to = $this->_image_rewrite($file['tmp_name'], $file['ext'], $save_to);
            if ($this->config['image_auto_cut']) {
                $save_to = $real_save_to = $this->_image_auto_cut($save_to); // 图片自动裁剪
            }
        } elseif (!move_uploaded_file($file['tmp_name'], $real_save_to) && is_file($real_save_to)) {
            $this->error = '文件上传失败';
            return [];
        }
        unset($file['tmp_name']);
        $file['path'] = substr($save_to, strlen(ROOT_PATH . '/public'));
        $file['size'] = filesize($real_save_to);
        $file['file_type'] = $file_type;
        $file['api_type'] = $this->config['api_type'];
        $file['upload_time'] = time();
        return $file;
    }

    // 根据文件后缀获取文件类型
    protected function _file_type(string $allow_type, string $ext): string
    {
        if ($allow_type == '*') {
            foreach ($this->type as $type => $exts) {
                if (in_array($ext, explode('|', $exts))) {
                    return $type;
                }
            }
        }
        return $allow_type;
    }

    // 重写图片
    protected function _image_rewrite(string $tmp_name, string $ext, string $save_file): string
    {
        $ext = trim($ext, '.');
        if ($ext == 'png') {
            $img = @imagecreatefrompng($tmp_name);
            imagesavealpha($img, true);
            imagepng($img, $save_file, 9);
        } elseif ($ext == 'gif') {
            $img = imagecreatefromgif($tmp_name);
            imagegif($img, $save_file);
        } else {
            $img = imagecreatefromjpeg($tmp_name);
            imagejpeg($img, $save_file, 100);
        }
        imagedestroy($img);
        return $save_file;
    }

    // 图片自动裁剪
    protected function _image_auto_cut(string $image_file): string
    {
        $cut_type = $this->config['image_cut']['type'] ?? 1; // 裁剪方式
        $max_width = $this->config['image_cut']['max_width'] ?? 980; // 最大宽度
        $width = $this->config['image_cut']['width'] ?? 0; // 宽度
        $height = $this->config['image_cut']['height'] ?? 0; // 高度
        $is_cut = false; // 是否裁剪
        $image_info = getimagesize($image_file);
        if ($max_width > 0 && $image_info[0] > $max_width) {
            $width = $max_width;
            $cut_type = 1;
            $is_cut = true;
        } elseif ($width > 0 || $height > 0) {
            $is_cut = true;
        }
        if ($is_cut) {
            $cut_file = dirname($image_file) . '/w' . $width . 'h' . $height . '_' . basename($image_file);
            $cut_ok = Thumb::init()->make($image_file, $cut_file, $width, $height, $cut_type);
            if ($cut_ok) {
                unlink($image_file);
                return $cut_file;
            }
        }
        return $image_file;
    }

    // 获取错误信息
    public function getError(): string
    {
        return $this->error;
    }
}