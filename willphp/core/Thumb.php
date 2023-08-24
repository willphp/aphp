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
class Thumb
{
    use Single;

    public function getThumb(string $image, int $width, int $height, int $thumbType = 6): string
    {
        if (empty($image)) {
            return '';
        }
        $path = $image;
        $host = '/';
        if (false !== filter_var($image, FILTER_VALIDATE_URL)) {
            $path = parse_url($image, PHP_URL_PATH);
            $host = substr($image, 0, -strlen($path)) . '/';
        }
        $path = ltrim($path, '/');
        $root = ROOT_PATH . '/public';
        $file = pathinfo($path);
        $thumb = $file['dirname'] . '/' . $file['filename'] . '_thumb_w' . $width . 'h' . $height . '.' . $file['extension'];
        $srcFile = $root . '/' . $path;
        $outFile = $root . '/' . $thumb;
        if (file_exists($outFile)) {
            return $host . $thumb;
        }
        $isThumb = $this->make($srcFile, $outFile, $width, $height, $thumbType);
        return $isThumb ? $host . $thumb : $host . $path;
    }

    public function make(string $imgFile, string $outFile, int $thumbWidth, int $thumbHeight, int $thumbType = 6): bool
    {
        if (is_file($imgFile) && $imgInfo = getimagesize($imgFile)) {
            $thumbSize = $this->getThumbSize($imgInfo[0], $imgInfo[1], $thumbWidth, $thumbHeight, $thumbType);
            $thumbSize = array_map(fn($v):int=>intval($v), $thumbSize);
            $imgType = image_type_to_extension($imgInfo[2], false);
            $funcCreate = 'imagecreatefrom' . $imgType;
            $funcOut = 'image' . $imgType;
            $funcCopy = function_exists('imagecopyresampled') ? 'imagecopyresampled' : 'imagecopyresized';
            $imgRes = $funcCreate($imgFile);
            if ($imgType == 'gif') {
                $thumbRes = imagecreate($thumbSize[0], $thumbSize[1]);
            } else {
                $thumbRes = imagecreatetruecolor($thumbSize[0], $thumbSize[1]);
                imagealphablending($thumbRes, false); //关闭混色
                imagesavealpha($thumbRes, true); //储存透明通道
            }
            $funcCopy($thumbRes, $imgRes, 0, 0, 0, 0, $thumbSize[0], $thumbSize[1], $thumbSize[2], $thumbSize[3]);
            if ($imgType == 'gif') {
                $color = imagecolorallocate($thumbRes, 255, 0, 0);
                imagecolortransparent($thumbRes, $color);
            }
            is_dir(dirname($outFile)) or mkdir(dirname($outFile), 0755, true);
            $result = $funcOut($thumbRes, $outFile);
            if (isset($imgRes)) {
                imagedestroy($imgRes);
            }
            if (isset($thumbRes)) {
                imagedestroy($thumbRes);
            }
            return $result;
        }
        return false;
    }

    private function getThumbSize(int $imgWidth, int $imgHeight, int $thumbWidth, int $thumbHeight, int $thumbType): array
    {
        //初始化缩略图尺寸
        $tw = $thumbWidth;
        $th = $thumbHeight;
        //初始化原图尺寸
        $iw = $imgWidth;
        $ih = $imgHeight;
        switch ($thumbType) {
            case 1 :
                //固定宽度  高度自增
                $th = intval($thumbWidth / $imgWidth * $imgHeight);
                break;
            case 2 :
                //固定高度  宽度自增
                $tw = intval($thumbHeight / $imgHeight * $imgWidth);
                break;
            case 3 :
                //固定宽度  高度裁切
                $ih = intval($imgWidth / $thumbWidth * $thumbHeight);
                break;
            case 4 :
                //固定高度  宽度裁切
                $iw = intval($imgHeight / $thumbHeight * $thumbWidth);
                break;
            case 5 :
                //缩放最大边 原图不裁切
                if (($imgWidth / $thumbWidth) > ($imgHeight / $thumbHeight)) {
                    $th = intval($thumbWidth / $imgWidth * $imgHeight);
                } elseif (($imgWidth / $thumbWidth) < ($imgHeight / $thumbHeight)) {
                    $tw = intval($thumbHeight / $imgHeight * $imgWidth);
                }
                break;
            default:
                //缩略图尺寸不变，自动裁切图片
                if (($imgHeight / $thumbHeight) < ($imgWidth / $thumbWidth)) {
                    $iw = intval($imgHeight / $thumbHeight * $thumbWidth);
                } elseif (($imgHeight / $thumbHeight) > ($imgWidth / $thumbWidth)) {
                    $ih = intval($imgWidth / $thumbWidth * $thumbHeight);
                }
        }
        return [$tw, $th, $iw, $ih];
    }
}