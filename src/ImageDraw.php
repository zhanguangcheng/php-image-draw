<?php

/**
 * 简单的图片绘画类
 * 1、通过图片或真彩色创建画布
 * 2、绘画文本、图片、直线、圆形、矩形、多边形
 * 3、保存到文件或返回图片内容
 */
class ImageDraw
{
    // 输入输出
    const TYPE_FROM_IMAGE = 'fromImage';
    const TYPE_FROM_TRUE_COLOR = 'fromTrueColor';
    const TYPE_TO_FILE = 'toFile';
    const TYPE_TO_STREAM = 'toStream';

    // 绘画类型
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_LINE = 'line';
    const TYPE_ELLIPSE = 'ellipse';
    const TYPE_RECTANGLE = 'rectangle';
    const TYPE_POLYGON = 'polygon';

    // 图片资源对象
    public $image;
    public $imageWidth = 0;
    public $imageHeight = 0;

    public static function getTypes()
    {
        return [
            self::TYPE_FROM_IMAGE => '从图片创建',
            self::TYPE_FROM_TRUE_COLOR => '从真彩色创建',
            self::TYPE_TO_FILE => '输出为文件',
            self::TYPE_TO_STREAM => '输出为流',

            self::TYPE_TEXT => '文本',
            self::TYPE_IMAGE => '图片',
            self::TYPE_LINE => '直线',
            self::TYPE_ELLIPSE => '圆形',
            self::TYPE_RECTANGLE => '矩形',
            self::TYPE_POLYGON => '多边形',
        ];
    }

    public function drawWithConfig($config)
    {
        $result = null;
        foreach ($config as $item) {
            if (empty($item['type'])) {
                throw new InvalidArgumentException('参数type不能为空');
            }
            if (!array_key_exists($item['type'], self::getTypes())) {
                throw new InvalidArgumentException("参数type（${$item['type']}）暂未支持");
            }
            $method = 'draw' . ucfirst($item['type']);
            if ($item['type'] === self::TYPE_TO_STREAM) {
                $result = $this->$method($item);
            } else {
                $this->$method($item);
            }
        }
        return $result;
    }

    public function drawFromTrueColor($item)
    {
        $image = imagecreatetruecolor($item['width'], $item['height']);
        $this->image = $image;
        $this->imageWidth = $item['width'];
        $this->imageHeight = $item['height'];

        $color = $this->getColor($item, [255, 255, 255]);
        imagefill($image, 0, 0, $color);
        imagecolordeallocate($image, $color);
        return $this;
    }

    public function drawFromImage($item)
    {
        $image = $this->createImage($item['file'], $size);
        $this->image = $image;
        $this->imageWidth = $size[0];
        $this->imageHeight = $size[1];
        return $this;
    }

    public function drawText($item)
    {
        $color = $this->getColor($item);
        imagettftext($this->image, $item['fontSize'], 0, $this->getX($item, self::TYPE_TEXT), $item['y'], $color, $item['font'], $item['text']);
        imagecolordeallocate($this->image, $color);
        return $this;
    }

    public function drawImage($item)
    {
        $image = $this->createImage($item['file'], $size);
        imagecopyresampled($this->image, $image, $this->getX($item, self::TYPE_IMAGE), $item['y'], 0, 0, $item['width'], $item['height'], $size[0], $size[1]);
        imagedestroy($image);
        return $this;
    }

    public function drawLine($item)
    {
        $color = $this->getColor($item);
        imageline($this->image, $item['x1'], $item['y1'], $item['x2'], $item['y2'], $color);
        imagecolordeallocate($this->image, $color);
        return $this;
    }

    public function drawEllipse($item)
    {
        $color = $this->getColor($item);
        if (empty($item['fill'])) {
            imageellipse($this->image, $item['x'], $item['y'], $item['width'], $item['height'], $color);
        } else {
            imagefilledellipse($this->image, $item['x'], $item['y'], $item['width'], $item['height'], $color);
        }
        imagecolordeallocate($this->image, $color);
        return $this;
    }

    public function drawRectangle($item)
    {
        $color = $this->getColor($item);
        if (empty($item['fill'])) {
            imagerectangle($this->image, $item['x1'], $item['y1'], $item['x2'], $item['y2'], $color);
        } else {
            imagefilledrectangle($this->image, $item['x1'], $item['y1'], $item['x2'], $item['y2'], $color);
        }
        imagecolordeallocate($this->image, $color);
        return $this;
    }

    public function drawPolygon($item)
    {
        $color = $this->getColor($item);
        $count  = count($item['points']) / 2;
        if (empty($item['fill'])) {
            imagepolygon($this->image, $item['points'], $count, $color);
        } else {
            imagefilledpolygon($this->image, $item['points'], $count, $color);
        }
        imagecolordeallocate($this->image, $color);
        return $this;
    }

    public function drawToFile($item)
    {
        $ext = strrchr($item['filename'], '.');
        switch ($ext) {
            case '.png':
                imagepng($this->image, $item['filename']);
                break;
            case '.gif':
                imagegif($this->image, $item['filename']);
                break;
            case '.jpg':
            case '.jpeg':
                imagejpeg($this->image, $item['filename']);
                break;
            default:
                throw new InvalidArgumentException('暂未支持的图片类型：' . $ext);
                break;
        }
        imagedestroy($this->image);
        return true;
    }

    public function drawToStream($item)
    {
        ob_start();
        switch ($item['filetype']) {
            case 'png':
                imagepng($this->image);
                break;
            case 'gif':
                imagegif($this->image);
                break;
            case 'jpg':
            case 'jpeg':
                imagejpeg($this->image);
                break;
            default:
                throw new InvalidArgumentException('暂未支持的图片类型：' . $item['filetype']);
                break;
        }
        imagedestroy($this->image);
        return ob_get_clean();
    }

    protected function createImage($file, &$size = null)
    {
        $size = getimagesize($file);
        switch ($size['mime']) {
            case 'image/png':
                $image = @imagecreatefrompng($file);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($file);
                break;
            case 'image/jpg':
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($file);
                break;
            default:
                throw new InvalidArgumentException('暂未支持的图片类型：' . $size['mime']);
                break;
        }
        return $image;
    }

    protected function getColor($item, $default = [0, 0, 0])
    {
        $color = isset($item['color']) ? $item['color'] : $default;
        return imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
    }

    protected function getX($item, $type)
    {
        $x = $item['x'];
        if ($x === 'center') {
            switch ($type) {
                case self::TYPE_TEXT:
                    $box = imagettfbbox($item['fontSize'], 0, $item['font'], $item['text']);
                    $x = ($this->imageWidth - $box[2]) / 2;
                    break;
                case self::TYPE_IMAGE:
                    $x = ($this->imageWidth - $item['width']) / 2;
                    break;
            }
        }
        if (isset($item['xoffset'])) {
            $x += $item['xoffset'];
        }
        return (int) $x;
    }
}
