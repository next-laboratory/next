<?php


class Captcha
{

    private int $width;
    private int $height;
    private $number;
    private $code;
    private $img;
    //private $disturbColorNum = 1500;
    //创建对象自动设置三个参数
    function __construct($width = 120, $height = 50, $number = 4)
    {
        $this->width = $width;
        $this->height = $height;
        $this->number = $number;
    }

    function __toString()
    {
        $this->creatImg();
        $this->outImg();
        $_SESSION["code"] = strtolower($this->code);
        return '';
    }

    //创建图像
    private function creatImg()
    {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $background = imagecolorallocate($this->img, 255, 255, 255);
        imagefill($this->img, 0, 0, $background);
        //$this->randLetter();
        $font = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'index' . DIRECTORY_SEPARATOR . 'font' . DIRECTORY_SEPARATOR . 'simyou.ttf';
        for ($i = 0; $i < $this->number; $i++) {
            imagettftext($this->img, $this->height / 2, rand(15, -15), $this->width * 0.2 * $i, $this->height * 0.8, $this->randColor(), $font, $this->randLetter());
        }
        for ($i = 0; $i < $this->disturb(); $i++) {
            imagesetpixel($this->img, rand(0, $this->width), rand(0, $this->height), $this->randColor());
        }
        for ($i = 0; $i < rand(3, 8); $i++) {
            imagearc($this->img, rand(0, $this->width), rand(0, $this->height), rand(0, $this->width), rand(0, $this->height), rand(0, 360), rand(0, 360), $this->randColor());
        }
    }

    private function randLetter()
    {
        $string = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMOPQRSTUVWXYZ";
        $rand = rand(0, strlen($string) - 1);
        $this->code .= $string[$rand];
        return $string[$rand];
    }

    //随机颜色
    private function randColor()
    {
        return imagecolorallocate($this->img, rand(0, 127), rand(0, 127), rand(0, 127));
    }

    //混合点的数量占比
    private function disturb()
    {
        return $this->height * $this->width / 6;
    }

    /**
     * 以下不必要
     */
    private function outImg()
    {
        if (function_exists("imagepng")) {
            header('Content-type:image/png');
            imagepng($this->img);
        } elseif (function_exists("imagegif")) {
            header('Content-type:image/gif');
            imagegif($this->img);
        } elseif (function_exists("imagejpeg")) {
            header('Content-type:image/jpeg');
            imagejpeg($this->img);
        } elseif (function_exists("imagewbmp")) {
            header('Content-type:image/wbmp');
            imagewbmp($this->img);
        } else echo "系统不支持图像输出！";
    }

    function __destruct()
    {
        imagedestroy($this->img);
    }
}
