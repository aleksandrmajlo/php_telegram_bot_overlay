<?php

namespace App\Services;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;
class ImageTextJostService
{
    public function addTextToImage(string $imagePath, string $text): void
    {
        $imageManager = ImageManager::gd();
        $image = $imageManager->read($imagePath);

        $imageWidth = $image->width();
        $imageHeight = $image->height();

        // Вычисляем процент размера шрифта по высоте изображения
        if ($imageHeight > 2000) {
            $fontSize = intval($imageHeight * 0.018); // очень большое изображение
        } elseif ($imageHeight > 1000) {
            $fontSize = intval($imageHeight * 0.025);
        } elseif ($imageHeight > 500) {
            $fontSize = intval($imageHeight * 0.035);
        } else {
            $fontSize = intval($imageHeight * 0.04); // по умолчанию
        }
        $fontSize = max(10, $fontSize);
        // Координаты правого нижнего угла с отступом
        $x = $imageWidth - 30;
        $y = $imageHeight - 20;
        $wrapWidth = intval($imageWidth * 0.9);
        $image->text($text, $x, $y, function (FontFactory $font) use ($fontSize,$wrapWidth) {
            $font->filename(public_path('fonts/Jost-VariableFont_wght.ttf'));
            $font->size($fontSize);
            $font->color('#ffffff');
            $font->stroke('62655e', 1);
            $font->align('right');   // Выравнивание по правому краю
            $font->valign('bottom'); // И по нижнему краю
            $font->wrap($wrapWidth);
        });
        $image->save($imagePath);
    }
}
