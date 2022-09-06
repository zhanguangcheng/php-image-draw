# php-image-draw
 简单的图片绘画类

# 使用方法（方法调用）

## 导入、实例化类
```php
require './src/ImageDraw.php';

$draw = new ImageDraw();
```

## 创建画布

创建真彩色画布
```php
$draw->drawFromTrueColor(['width' => 300, 'height' => 300]);
```

使用图片作为背景创建画布
```php
$draw->drawFromImage(['file' => '/path/to/image.png']);
```

## 绘画
```php
// 写文字，左右居中显示
$font = '/path/to/font.ttf';
$draw->drawText(['text'=>'文本Test', 'x'=>'center', 'y'=>100, 'font'=>$font, 'fontSize'=>20]);

// 画图片
$draw->drawImage(['file' => '/path/to/image.png', 'x' => 100, 'y' => 100, 'width' => 100, 'height' => 100]);

// 画直线
$draw->drawLine(['x1'=>10, 'y1'=>10, 'x2'=>20, 'y2'=>20, 'color'=>[255, 0, 0]]);

// 画圆和填充的圆
$draw->drawEllipse(['x' => 100, 'y' => 100, 'width' => 50, 'height' => 50, 'color' => [255, 0, 0]]);
$draw->drawEllipse(['fill' => true, 'x' => 100, 'y' => 100, 'width' => 50, 'height' => 50, 'color' => [255, 0, 0]]);

// 画矩形和填充的矩形
$draw->drawRectangle(['x' => 100, 'y' => 100, 'width' => 50, 'height' => 50]);
$draw->drawRectangle(['fill' => true, 'x' => 100, 'y' => 100, 'width' => 50, 'height' => 50]);

// 多边形
$draw->drawPolygon(['points' => [
    10,10,
    10,40,
    50,40,
    20,10,
]]);
```

## 输出图片

保存为图片
```php
$draw->drawToFile(['filename' => __FILE__ . '.png']);
```

获取图片内容
```php
$image = $draw->drawToStream(['filetype' => 'png']);
```


# 使用方法（配置化）

```php
require './src/ImageDraw.php';
$draw = new ImageDraw();

$draw->drawWithConfig([
    ['type' => ImageDraw::TYPE_FROM_TRUE_COLOR, 'width' => 500, 'height' => 500],
    ['type' => ImageDraw::TYPE_TEXT, 'text'=>'文本Test', 'x'=>'center', 'y'=>100, 'font'=>$font, 'fontSize'=>20],
    ['type' => ImageDraw::TYPE_IMAGE, 'file' => '/path/to/image.png', 'x' => 100, 'y' => 100, 'width' => 100, 'height' => 100],
    ['type' => ImageDraw::TYPE_TO_FILE, 'file' => __FILE__ . '.png'],
]);
```
